<?php

namespace micro\cache;

use mindplay\annotations\Annotations;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;
use micro\controllers\Router;
use micro\utils\FsUtils;
use micro\cache\traits\RouterCacheTrait;
use micro\cache\traits\ModelsCacheTrait;
use micro\cache\traits\RestCacheTrait;

class CacheManager {
	use RouterCacheTrait,ModelsCacheTrait,RestCacheTrait;

	public static $cache;
	private static $cacheDirectory;

	public static function start(&$config) {
		self::$cacheDirectory=self::initialGetCacheDirectory($config);
		$cacheDirectory=ROOT . DS . self::$cacheDirectory;
		Annotations::$config['cache']=new AnnotationCache($cacheDirectory . '/annotations');
		self::register(Annotations::getManager());
		self::getCacheInstance($config, $cacheDirectory, ".cache");
	}

	public static function startProd(&$config) {
		self::$cacheDirectory=self::initialGetCacheDirectory($config);
		$cacheDirectory=ROOT . DS . self::$cacheDirectory;
		self::getCacheInstance($config,$cacheDirectory, ".cache");
	}

	protected static function getCacheInstance(&$config,$cacheDirectory,$postfix){
		$cacheSystem='micro\cache\system\ArrayCache';
		if(!isset(self::$cache)){
			if(isset($config["cache"]["system"])){
				$cacheSystem=$config["cache"]["system"];
			}
			self::$cache=new $cacheSystem($cacheDirectory,$postfix);
		}
		return self::$cache;
	}

	private static function initialGetCacheDirectory(&$config) {
		$cacheDirectory=@$config["cache"]["directory"];
		if (!isset($cacheDirectory)) {
			$config["cache"]["directory"]="cache/";
			$cacheDirectory=$config["cache"]["directory"];
		}
		return $cacheDirectory;
	}

	public static function getCacheDirectory() {
		return self::$cacheDirectory;
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

	protected static function _getFiles(&$config,$type,$silent=false){
		$typeNS=$config["mvcNS"][$type];
		$typeDir=ROOT . DS . str_replace("\\", DS, $typeNS);
		if(!$silent)
			echo \ucfirst($type)." directory is " . ROOT . $typeNS . "\n";
		return FsUtils::glob_recursive($typeDir . DS . '*');
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
}
