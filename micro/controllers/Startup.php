<?php
namespace micro\controllers;
use micro\orm\DAO;
use micro\utils\StrUtils;
use micro\views\engine\TemplateEngine;

class Startup{
	public static $urlParts;
	private static $config;
	public static function run(array &$config,$url){
		@set_exception_handler(array('Startup', 'errorHandler'));
		try {
			$engineOptions=array('cache' => ROOT.DS."views/cache/");
			if(array_key_exists("templateEngine", $config)){
				if(\array_key_exists("templateEngineOptions", $config)){
					$engineOptions=$config["templateEngineOptions"];
				}
				$engine=new $config["templateEngine"]($engineOptions);
				if ($engine instanceof TemplateEngine){
					$config["templateEngine"]=$engine;
				}
			}
		} catch (\Exception $e) {
			echo $e->getTraceAsString();
		}
		session_start();

		if($config["test"]){
			\micro\log\Logger::init();
			$config["siteUrl"]="http://127.0.0.1:8090/";
		}

		$db=$config["database"];
		if($db["dbName"]!=="")
			DAO::connect($db["dbName"],@$db["serverName"],@$db["port"],@$db["user"],@$db["password"]);

		if(!$url){
			$url=$config["documentRoot"];
		}
		if(StrUtils::endswith($url, "/"))
			$url=substr($url, 0,strlen($url)-1);
		self::$urlParts=explode("/", $url);

		$u=self::$urlParts;

		if(class_exists($u[0]) && StrUtils::startswith($u[0],"_")===false){
			//Construction de l'instance de la classe (1er élément du tableau)
			try{
				if(array_key_exists("onStartup", $config)){
					if(is_callable($config['onStartup'])){
						$config["onStartup"]($u);
					}
				}
				self::runAction($u);
			}catch (\Exception $e){
				print "Error!: " . $e->getMessage() . "<br/>";
			}

		}else{
			print "Le contrôleur `".$u[0]."` n'existe pas <br/>";
		}
	}

	public static function runAction($u,$initialize=true,$finalize=true){
		$urlSize=sizeof($u);
		$obj=new $u[0]();
		$config=self::getConfig();
		//Dependency injection
		if(\array_key_exists("di", $config)){
			$di=$config["di"];
			if(\is_array($di)){
				foreach ($di as $k=>$v){
					$obj->$k=$v();
				}
			}
		}
		if($initialize)
			$obj->initialize();
		try{
			switch ($urlSize) {
				case 1:
					$obj->index();
					break;
				case 2:
					$action=$u[1];
					//Appel de la méthode (2ème élément du tableau)
					if(method_exists($obj, $action)){
						$obj->$action();
					}else{
						print "La méthode `{$action}` n'existe pas sur le contrôleur `".$u[0]."`<br/>";
					}
					break;
				default:
					//Appel de la méthode en lui passant en paramètre le reste du tableau
					\call_user_func_array(array($obj,$u[1]), array_slice($u, 2));
					break;
			}
		}catch (\Exception $e){
			print "Error!: " . $e->getMessage() . "<br/>";
		}
		if($finalize)
			$obj->finalize();
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
