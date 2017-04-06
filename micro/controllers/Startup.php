<?php
namespace micro\controllers;
use micro\orm\DAO;
use micro\utils\StrUtils;
use micro\views\engine\TemplateEngine;
use mindplay\annotations\Annotations;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;
use micro\orm\OrmUtils;

class Startup{
	public static $urlParts;
	private static $config;

	public static function run(array &$config,$url){
		@set_exception_handler(array('Startup', 'errorHandler'));
		self::$config=$config;
		self::startTemplateEngine($config);

		if($config["test"]){
			\micro\log\Logger::init();
			$config["siteUrl"]="http://127.0.0.1:8090/";
		}

		$db=$config["database"];
		if($db["dbName"]!==""){
			DAO::connect($db["dbName"],@$db["serverName"],@$db["port"],@$db["user"],@$db["password"]);
			self::startOrm($config);
		}
		session_start();

		$u=self::parseUrl($config, $url);
		if(class_exists($config["mvcNS"]["controllers"].$u[0]) && StrUtils::startswith($u[0],"_")===false){
			//Construction de l'instance de la classe (1er élément du tableau)
			try{
				if(isset($config['onStartup'])){
					if(is_callable($config['onStartup'])){
						$config["onStartup"]($u);
					}
				}
				self::runAction($u);
			}catch (\Exception $e){
				print "Error!: " . $e->getMessage() . "<br/>";
			}
		}else{
			print "Le contrôleur `".$config["controllerNS"].$u[0]."` n'existe pas <br/>";
		}
	}

	public static function getCacheDirectory($config){
		$config=self::$config;
		$cacheDirectory=@$config["ormCache"]["cacheDirectory"];
		if(!isset($cacheDirectory)){
			self::$config["ormCache"]=["cacheDirectory"=>"models/cache/"];
			$cacheDirectory=self::$config["ormCache"]["cacheDirectory"];
		}
		return $cacheDirectory;
	}

	private static function startOrm($config){
		$cacheDirectory=ROOT.DS.self::getCacheDirectory($config);
		Annotations::$config['cache'] = new AnnotationCache($cacheDirectory.'/annotations');
		self::register(Annotations::getManager());
		OrmUtils::$ormCache=new AnnotationCache($cacheDirectory);
	}

	private static function register(AnnotationManager $annotationManager){
		$annotationManager->registry=array_merge($annotationManager->registry,[
				'id' => 'micro\annotations\IdAnnotation',
				'manyToOne' => 'micro\annotations\ManyToOneAnnotation',
				'oneToMany' => 'micro\annotations\OneToManyAnnotation',
				'manyToMany' => 'micro\annotations\ManyToManyAnnotation',
				'joinColumn' => 'micro\annotations\JoinColumnAnnotation',
				'table' => 'micro\annotations\TableAnnotation',
				'transient' => 'micro\annotations\TransientAnnotation',
				'column' => 'micro\annotations\ColumnAnnotation',
				'joinTable' => 'micro\annotations\JoinTableAnnotation'
		]);
	}

	private static function parseUrl($config,$url){
		if(!$url){
			$url=$config["documentRoot"];
		}
		if(StrUtils::endswith($url, "/"))
			$url=substr($url, 0,strlen($url)-1);
		self::$urlParts=explode("/", $url);

		return self::$urlParts;
	}

	private static function startTemplateEngine($config){
		try {
			$engineOptions=array('cache' => ROOT.DS."views/cache/");
			if(isset($config["templateEngine"])){
				$templateEngine=$config["templateEngine"];
				if(isset($config["templateEngineOptions"])){
					$engineOptions=$config["templateEngineOptions"];
				}
				$engine=new $templateEngine($engineOptions);
				if ($engine instanceof TemplateEngine){
					self::$config["templateEngine"]=$engine;
				}
			}
		} catch (\Exception $e) {
			echo $e->getTraceAsString();
		}
	}

	public static function runAction($u,$initialize=true,$finalize=true){
		$config=self::getConfig();
		$ctrl=$config["mvcNS"]["controllers"].$u[0];
		$controller=new $ctrl();
		if(!$controller instanceof Controller){
			print "`{$u[0]}` n'est pas une instance de contrôleur.`<br/>";
			return;
		}
		//Dependency injection
		if(\array_key_exists("di", $config)){
			$di=$config["di"];
			if(\is_array($di)){
				foreach ($di as $k=>$v){
					$controller->$k=$v();
				}
			}
		}

		if($initialize)
			$controller->initialize();
		self::callController($controller,$u);
		if($finalize)
			$controller->finalize();
	}

	private static function callController(Controller $controller,$u){
		$urlSize=sizeof($u);
		try{
			switch ($urlSize) {
				case 1:
					$controller->index();
					break;
				case 2:
					$action=$u[1];
					//Appel de la méthode (2ème élément du tableau)
					if(method_exists($controller, $action)){
						$controller->$action();
					}else{
						print "La méthode `{$action}` n'existe pas sur le contrôleur `".$u[0]."`<br/>";
					}
					break;
				default:
					//Appel de la méthode en lui passant en paramètre le reste du tableau
					\call_user_func_array(array($controller,$u[1]), array_slice($u, 2));
					break;
			}
		}catch (\Exception $e){
			print "Error!: " . $e->getMessage() . "<br/>";
		}
	}

	public static function getConfig(){
		return self::$config;
	}

	public static function errorHandler($severity, $message, $filename, $lineno) {
		if (error_reporting() == 0) {
			return;
		}
		if (error_reporting() & $severity) {
			throw new \ErrorException($message, 0, $severity, $filename, $lineno);
		}
	}
}
