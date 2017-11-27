<?php

namespace micro\cache;

use mindplay\annotations\Annotations;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;
use micro\orm\parser\ModelParser;
use micro\utils\JArray;
use micro\controllers\Router;
use micro\controllers\Startup;
use micro\utils\FsUtils;
use micro\cache\parser\ControllerParser;
use micro\cache\parser\RestControllerParser;

class CacheManager {
	public static $cache;
	private static $routes=[ ];
	private static $cacheDirectory;
	private static $expiredRoutes=[ ];

	public static function start(&$config) {
		self::$cacheDirectory=self::initialGetCacheDirectory($config);
		$cacheDirectory=ROOT . DS . self::$cacheDirectory;
		Annotations::$config['cache']=new AnnotationCache($cacheDirectory . '/annotations');
		self::register(Annotations::getManager());
		self::$cache=new ArrayCache($cacheDirectory, ".cache");
	}

	public static function startProd(&$config) {
		self::$cacheDirectory=self::initialGetCacheDirectory($config);
		$cacheDirectory=ROOT . DS . self::$cacheDirectory;
		self::$cache=new ArrayCache($cacheDirectory, ".cache");
	}

	public static function getControllerCache($isRest=false) {
		$key=($isRest)?"rest":"default";
		if (self::$cache->exists("controllers/routes.".$key))
			return self::$cache->fetch("controllers/routes.".$key);
		return [ ];
	}

	public static function getRestCache() {
		if (self::$cache->exists("controllers/rest"))
			return self::$cache->fetch("controllers/rest");
		return [ ];
	}

	public static function getRouteCache($routePath, $duration) {
		$key=self::getRouteKey($routePath);

		if (self::$cache->exists("controllers/" . $key) && !self::expired($key, $duration)) {
			$response=self::$cache->file_get_contents("controllers/" . $key);
			return $response;
		} else {
			$response=Startup::runAsString($routePath);
			return self::storeRouteResponse($key, $response);
		}
	}

	public static function expired($key, $duration) {
		return self::$cache->expired("controllers/" . $key, $duration) === true || \array_key_exists($key, self::$expiredRoutes);
	}

	public static function isExpired($path,$duration){
		$route=Router::getRoute($path,false);
		if($route!==false && \is_array($route)){
			return self::expired(self::getRouteKey($route), $duration);
		}
		return true;
	}

	public static function setExpired($routePath, $expired=true) {
		$key=self::getRouteKey($routePath);
		self::setKeyExpired($key, $expired);
	}

	private static function setKeyExpired($key, $expired=true) {
		if ($expired) {
			self::$expiredRoutes[$key]=true;
		} else {
			unset(self::$expiredRoutes[$key]);
		}
	}

	public static function setRouteCache($routePath) {
		$key=self::getRouteKey($routePath);
		$response=Startup::runAsString($routePath);
		return self::storeRouteResponse($key, $response);
	}

	private static function storeRouteResponse($key, $response) {
		self::setKeyExpired($key, false);
		self::$cache->store("controllers/" . $key, $response, false);
		return $response;
	}

	private static function getRouteKey($routePath) {
		return "path" . \md5(\implode("", $routePath));
	}

	private static function initialGetCacheDirectory(&$config) {
		$cacheDirectory=@$config["cacheDirectory"];
		if (!isset($cacheDirectory)) {
			$config["cacheDirectory"]="cache/";
			$cacheDirectory=$config["cacheDirectory"];
		}
		return $cacheDirectory;
	}

	public static function getCacheDirectory() {
		return self::$cacheDirectory;
	}

	public static function createOrmModelCache($classname) {
		$key=self::getModelCacheKey($classname);
		if(isset(self::$cache)){
			if (!self::$cache->exists($key)) {
				$p=new ModelParser();
				$p->parse($classname);
				self::$cache->store($key, $p->__toString());
			}
			return self::$cache->fetch($key);
		}
	}

	public static function getModelCacheKey($classname){
		return \str_replace("\\", DIRECTORY_SEPARATOR, $classname);
	}

	public static function modelCacheExists($classname){
		$key=self::getModelCacheKey($classname);
		if(isset(self::$cache))
			return self::$cache->exists($key);
		return false;
	}

	private static function addControllerCache($classname) {
		$parser=new ControllerParser();
		try {
			$parser->parse($classname);
			return $parser->asArray();
		} catch ( \Exception $e ) {
			// Nothing to do
		}
		return [];
	}

	public static function checkCache(&$config,$silent=false) {
		$dirs=self::getCacheDirectories($config,$silent);
		foreach ($dirs as $dir){
			self::safeMkdir($dir);
		}
		return $dirs;
	}

	public static function getCacheDirectories(&$config,$silent=false){
		$cacheDirectory=self::initialGetCacheDirectory($config);
		if(!$silent){
			echo "cache directory is " . FsUtils::cleanPathname(ROOT . DS . $cacheDirectory) . "\n";
		}
		$modelsDir=str_replace("\\", DS, $config["mvcNS"]["models"]);
		$controllersDir=str_replace("\\", DS, $config["mvcNS"]["controllers"]);
		$annotationCacheDir=ROOT . DS . $cacheDirectory . DS . "annotations";
		$modelsCacheDir=ROOT . DS . $cacheDirectory . DS . $modelsDir;
		$queriesCacheDir=ROOT . DS . $cacheDirectory . DS . "queries";
		$controllersCacheDir=ROOT . DS . $cacheDirectory . DS . $controllersDir;
		$viewsCacheDir=ROOT . DS . $cacheDirectory . DS . "views";
		return [ "annotations" => $annotationCacheDir,"models" => $modelsCacheDir,"controllers" => $controllersCacheDir,"queries" => $queriesCacheDir ,"views"=>$viewsCacheDir];
	}

	private static function safeMkdir($dir) {
		if (!is_dir($dir))
			return mkdir($dir, 0777, true);
	}

	public static function clearCache(&$config, $type="all") {
		$cacheDirectories=self::checkCache($config);
		$cacheDirs=["annotations","controllers","models","queries","views"];
		foreach ($cacheDirs as $typeRef){
			self::_clearCache($cacheDirectories, $type, $typeRef);
		}
	}

	private static function _clearCache($cacheDirectories,$type,$typeRef){
		if ($type === "all" || $type === $typeRef)
			FsUtils::deleteAllFilesFromFolder($cacheDirectories[$typeRef]);
	}

	public static function initCache(&$config, $type="all",$silent=false) {
		self::checkCache($config,$silent);
		self::start($config);
		if ($type === "all" || $type === "models")
			self::initModelsCache($config,false,$silent);
		if ($type === "all" || $type === "controllers")
			self::initRouterCache($config,$silent);
		if ($type === "all" || $type === "rest")
			self::initRestCache($config,$silent);
	}

	public static function initModelsCache(&$config,$forChecking=false,$silent=false) {
		$files=self::getModelsFiles($config,$silent);
		foreach ( $files as $file ) {
			if (is_file($file)) {
				$model=ClassUtils::getClassFullNameFromFile($file);
				if(!$forChecking){
					new $model();
				}
			}
		}
		if(!$silent){
			echo "Models cache reset\n";
		}
	}

	public static function getModelsFiles(&$config,$silent=false){
		return self::_getFiles($config, "models",$silent);
	}

	public static function getModels(&$config,$silent=false){
		$result=[];
		$files=self::getModelsFiles($config,$silent);
		foreach ($files as $file){
			$result[]=ClassUtils::getClassFullNameFromFile($file);
		}
		return $result;
	}

	public static function getControllersFiles(&$config,$silent=false){
		return self::_getFiles($config, "controllers",$silent);
	}

	private static function _getFiles(&$config,$type,$silent=false){
		$typeNS=$config["mvcNS"][$type];
		$typeDir=ROOT . DS . str_replace("\\", DS, $typeNS);
		if(!$silent)
			echo \ucfirst($type)." directory is " . ROOT . $typeNS . "\n";
		return FsUtils::glob_recursive($typeDir . DS . '*');
	}

	private static function initRouterCache(&$config,$silent=false) {
		$routes=["rest"=>[],"default"=>[]];
		$files=self::getControllersFiles($config);
		foreach ( $files as $file ) {
			if (is_file($file)) {
				$controller=ClassUtils::getClassFullNameFromFile($file);
				$parser=new ControllerParser();
				try {
					$parser->parse($controller);
					$ret= $parser->asArray();
					$key=($parser->isRest())?"rest":"default";
					$routes[$key]=\array_merge($routes[$key], $ret);
				} catch ( \Exception $e ) {
					// Nothing to do
				}

			}
		}
		self::$cache->store("controllers/routes.default", "return " . JArray::asPhpArray($routes["default"], "array") . ";");
		self::$cache->store("controllers/routes.rest", "return " . JArray::asPhpArray($routes["rest"], "array") . ";");
		if(!$silent){
			echo "Router cache reset\n";
		}
	}

	private static function initRestCache(&$config,$silent=false) {
		$restCache=[];
		$files=self::getControllersFiles($config);
		foreach ( $files as $file ) {
			if (is_file($file)) {
				$controller=ClassUtils::getClassFullNameFromFile($file);
				$parser=new RestControllerParser();
				$parser->parse($controller,$config);
				if($parser->isRest())
					$restCache=\array_merge($restCache,$parser->asArray());
			}
		}
		self::$cache->store("controllers/rest", "return " . JArray::asPhpArray($restCache, "array") . ";");
		if(!$silent){
			echo "Rest cache reset\n";
		}
	}

	private static function register(AnnotationManager $annotationManager) {
		$annotationManager->registry=array_merge($annotationManager->registry, [
				'id' => 'micro\annotations\IdAnnotation',
				'manyToOne' => 'micro\annotations\ManyToOneAnnotation',
				'oneToMany' => 'micro\annotations\OneToManyAnnotation',
				'manyToMany' => 'micro\annotations\ManyToManyAnnotation',
				'joinColumn' => 'micro\annotations\JoinColumnAnnotation',
				'table' => 'micro\annotations\TableAnnotation',
				'transient' => 'micro\annotations\TransientAnnotation',
				'column' => 'micro\annotations\ColumnAnnotation',
				'joinTable' => 'micro\annotations\JoinTableAnnotation',
				'route' => 'micro\annotations\router\RouteAnnotation',
				'var' => 'mindplay\annotations\standard\VarAnnotation',
				'yuml' => 'micro\annotations\YumlAnnotation',
				'rest' => 'micro\annotations\rest\RestAnnotation',
				'authorization' => 'micro\annotations\rest\AuthorizationAnnotation'
		]);
	}

	public static function addAdminRoutes() {
		self::addControllerCache("micro\controllers\Admin");
	}

	public static function getRoutes() {
		$result=self::getControllerCache();
		return $result;
	}

	public static function getRestRoutes() {
		$result=[];
		$restCache=self::getRestCache();
		foreach ($restCache as $controllerClass=>$restAttributes){
			if(isset($restCache[$controllerClass])){
				$result[$controllerClass]=["restAttributes"=>$restAttributes,"routes"=>self::getControllerRoutes($controllerClass,true)];
			}
		}
		return $result;
	}

	public static function getControllerRoutes($controllerClass,$isRest=false){
		$result=[];
		$ctrlCache=self::getControllerCache($isRest);
		foreach ($ctrlCache as $path=>$routeAttributes){
			if(isset($routeAttributes["controller"])){
				if($routeAttributes["controller"]===$controllerClass){
					$result[$path]=$routeAttributes;
				}
			}else{
				$firstValue=reset($routeAttributes);
				if(isset($firstValue)&&isset($firstValue["controller"])){
					if($firstValue["controller"]===$controllerClass){
						$result[$path]=$routeAttributes;
					}
				}
			}
		}
		return $result;
	}

	public static function getRestResource($controllerClass){
		$cache=self::getRestCache();
		if(isset($cache[$controllerClass])){
			return $cache[$controllerClass]["resource"];
		}
		return null;
	}

	public static function getRestCacheController($controllerClass){
		$cache=self::getRestCache();
		if(isset($cache[$controllerClass])){
			return $cache[$controllerClass];
		}
		return null;
	}


	public static function addRoute($path, $controller, $action="index", $methods=null, $name="") {
		$controllerCache=self::getControllerCache();
		Router::addRouteToRoutes($controllerCache, $path, $controller, $action, $methods, $name);
		self::$cache->store("controllers/routes", "return " . JArray::asPhpArray($controllerCache, "array") . ";");
	}
}
