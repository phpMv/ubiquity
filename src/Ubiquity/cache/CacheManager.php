<?php

namespace Ubiquity\cache;

use mindplay\annotations\Annotations;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\cache\traits\RouterCacheTrait;
use Ubiquity\cache\traits\ModelsCacheTrait;
use Ubiquity\cache\traits\RestCacheTrait;
use Ubiquity\cache\system\AbstractDataCache;

class CacheManager {
	use RouterCacheTrait,ModelsCacheTrait,RestCacheTrait;
	
	/**
	 *
	 * @var AbstractDataCache
	 */
	public static $cache;
	private static $cacheDirectory;

	public static function start(&$config) {
		self::$cacheDirectory = self::initialGetCacheDirectory ( $config );
		$cacheDirectory = \ROOT . \DS . self::$cacheDirectory;
		Annotations::$config ['cache'] = new AnnotationCache ( $cacheDirectory . '/annotations' );
		self::register ( Annotations::getManager () );
		self::getCacheInstance ( $config, $cacheDirectory, ".cache" );
	}

	/**
	 * Starts the cache for production
	 *
	 * @param array $config
	 */
	public static function startProd(&$config) {
		self::$cacheDirectory = self::initialGetCacheDirectory ( $config );
		$cacheDirectory = \ROOT . \DS . self::$cacheDirectory;
		self::getCacheInstance ( $config, $cacheDirectory, ".cache" );
	}

	protected static function getCacheInstance(&$config, $cacheDirectory, $postfix) {
		$cacheSystem = 'Ubiquity\cache\system\ArrayCache';
		$cacheParams = [ ];
		if (! isset ( self::$cache )) {
			if (isset ( $config ["cache"] ["system"] )) {
				$cacheSystem = $config ["cache"] ["system"];
			}
			if (isset ( $config ["cache"] ["params"] )) {
				$cacheParams = $config ["cache"] ["params"];
			}
			self::$cache = new $cacheSystem ( $cacheDirectory, $postfix, $cacheParams );
		}
		return self::$cache;
	}

	private static function initialGetCacheDirectory(&$config) {
		return $config ["cache"] ["directory"]??($config ["cache"] ["directory"] = "cache".\DS);
	}

	public static function getCacheDirectory() {
		return self::$cacheDirectory;
	}
	
	public static function getAbsoluteCacheDirectory(){
		return \ROOT.\DS.self::$cacheDirectory;
	}
	
	public static function getCacheSubDirectory($subDirectory) {
		return \ROOT.\DS.self::$cacheDirectory.\DS.$subDirectory;
	}

	public static function checkCache(&$config, $silent = false) {
		$dirs = self::getCacheDirectories ( $config, $silent );
		foreach ( $dirs as $dir ) {
			self::safeMkdir ( $dir );
		}
		return $dirs;
	}

	public static function getCacheDirectories(&$config, $silent = false) {
		$cacheDirectory = self::initialGetCacheDirectory ( $config );
		$rootDS=\ROOT . \DS ;
		if (! $silent) {
			echo "cache directory is " . UFileSystem::cleanPathname ( $rootDS. $cacheDirectory ) . "\n";
		}
		$cacheDirectory=$rootDS . $cacheDirectory . \DS;
		$modelsDir = str_replace ( "\\", \DS, $config ["mvcNS"] ["models"] );
		$controllersDir = str_replace ( "\\", \DS, $config ["mvcNS"] ["controllers"] );
		$annotationCacheDir = $cacheDirectory . "annotations";
		$modelsCacheDir = $cacheDirectory . $modelsDir;
		$queriesCacheDir = $cacheDirectory . "queries";
		$controllersCacheDir = $cacheDirectory . $controllersDir;
		$viewsCacheDir = $cacheDirectory . "views";
		$seoCacheDir = $cacheDirectory . "seo";
		$gitCacheDir = $cacheDirectory . "git";
		$contentsCacheDir = $cacheDirectory . "contents";
		return [ "annotations" => $annotationCacheDir,"models" => $modelsCacheDir,"controllers" => $controllersCacheDir,"queries" => $queriesCacheDir,"views" => $viewsCacheDir,"seo" => $seoCacheDir,"git" => $gitCacheDir,"contents"=>$contentsCacheDir ];
	}

	private static function safeMkdir($dir) {
		if (! is_dir ( $dir ))
			return mkdir ( $dir, 0777, true );
	}

	public static function clearCache(&$config, $type = "all") {
		$cacheDirectories = self::checkCache ( $config );
		$cacheDirs = [ "annotations","controllers","models","queries","views","contents" ];
		foreach ( $cacheDirs as $typeRef ) {
			self::_clearCache ( $cacheDirectories, $type, $typeRef );
		}
	}

	private static function _clearCache($cacheDirectories, $type, $typeRef) {
		if ($type === "all" || $type === $typeRef)
			UFileSystem::deleteAllFilesFromFolder ( $cacheDirectories [$typeRef] );
	}

	public static function initCache(&$config, $type = "all", $silent = false) {
		self::checkCache ( $config, $silent );
		self::start ( $config );
		if ($type === "all" || $type === "models")
			self::initModelsCache ( $config, false, $silent );
		if ($type === "all" || $type === "controllers")
			self::initRouterCache ( $config, $silent );
		if ($type === "all" || $type === "rest")
			self::initRestCache ( $config, $silent );
	}

	protected static function _getFiles(&$config, $type, $silent = false) {
		$typeNS = $config ["mvcNS"] [$type];
		$typeDir = \ROOT . \DS . str_replace ( "\\", \DS, $typeNS );
		if (! $silent)
			echo \ucfirst ( $type ) . " directory is " . \ROOT . $typeNS . "\n";
		return UFileSystem::glob_recursive ( $typeDir . \DS . '*' );
	}

	private static function register(AnnotationManager $annotationManager) {
		$annotationManager->registry = array_merge ( $annotationManager->registry, [ 
				'id' => 'Ubiquity\annotations\IdAnnotation',
				'manyToOne' => 'Ubiquity\annotations\ManyToOneAnnotation',
				'oneToMany' => 'Ubiquity\annotations\OneToManyAnnotation',
				'manyToMany' => 'Ubiquity\annotations\ManyToManyAnnotation',
				'joinColumn' => 'Ubiquity\annotations\JoinColumnAnnotation',
				'table' => 'Ubiquity\annotations\TableAnnotation',
				'transient' => 'Ubiquity\annotations\TransientAnnotation',
				'column' => 'Ubiquity\annotations\ColumnAnnotation',
				'validator' => 'Ubiquity\annotations\ValidatorAnnotation',
				'joinTable' => 'Ubiquity\annotations\JoinTableAnnotation',
				'requestMapping' => 'Ubiquity\annotations\router\RouteAnnotation',
				'route' => 'Ubiquity\annotations\router\RouteAnnotation',
				'get' => 'Ubiquity\annotations\router\GetAnnotation','getMapping' => 'Ubiquity\annotations\router\GetAnnotation',
				'post' => 'Ubiquity\annotations\router\PostAnnotation','postMapping' => 'Ubiquity\annotations\router\PostAnnotation',
				'var' => 'mindplay\annotations\standard\VarAnnotation',
				'yuml' => 'Ubiquity\annotations\YumlAnnotation',
				'rest' => 'Ubiquity\annotations\rest\RestAnnotation',
				'authorization' => 'Ubiquity\annotations\rest\AuthorizationAnnotation',
				'injected' =>'Ubiquity\annotations\di\InjectedAnnotation',
				'autowired' =>'Ubiquity\annotations\di\AutowiredAnnotation'
		] );
	}
}
