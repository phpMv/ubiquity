<?php
namespace Ubiquity\cache\traits;

use Ubiquity\utils\base\UFileSystem;
use Ubiquity\annotations\AnnotationsEngineInterface;

/**
 * To be Used in dev mode, not in production
 * Ubiquity\cache\traits$DevCacheTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.2
 *
 * @property string $cacheDirectory
 */
trait DevCacheTrait {

	private static AnnotationsEngineInterface $annotationsEngine;

	abstract protected static function getCacheInstance(&$config, $cacheDirectory, $postfix);

	abstract protected static function initRestCache(&$config, $silent = false);

	abstract protected static function initRouterCache(&$config, $silent = false);

	abstract public static function initModelsCache(&$config, $forChecking = false, $silent = false);

	private static function _getAnnotationsEngineInstance(){
		if(\class_exists('Ubiquity\\attributes\\AttributesEngine',true)){
			return new \Ubiquity\attributes\AttributesEngine();
		}elseif(\class_exists('Ubiquity\\annotations\\AnnotationsEngine',true)){
			return new \Ubiquity\annotations\AnnotationsEngine();
		}
	}

	public static function getAnnotationsEngineInstance(){
		return self::$annotationsEngine??=self::_getAnnotationsEngineInstance();
	}

	private static function initialGetCacheDirectory(&$config) {
		return $config['cache']['directory'] ??= 'cache' . \DS;
	}

	/**
	 * Starts the cache in dev mode, for generating the other caches
	 * Do not use in production
	 *
	 * @param array $config
	 */
	public static function start(&$config) {
		self::$cacheDirectory = self::initialGetCacheDirectory($config);
		$cacheDirectory = \ROOT . \DS . self::$cacheDirectory;
		self::getAnnotationsEngineInstance()->start($cacheDirectory);
		self::getCacheInstance($config, $cacheDirectory, '.cache')->init();
	}

	/**
	 *
	 * @param array $nameClasses
	 *        	an array of name=>class annotations
	 */
	public static function registerAnnotations(array $nameClasses): void {
		self::getAnnotationsEngineInstance()->registerAnnotations($nameClasses);
	}


	/**
	 * Checks the existence of cache subdirectories and returns an array of cache folders
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function checkCache(&$config, $silent = false) {
		$dirs = self::getCacheDirectories($config, $silent);
		foreach ($dirs as $dir) {
			self::safeMkdir($dir);
		}
		return $dirs;
	}

	/**
	 * Returns an associative array of cache folders (annotations, models, controllers, queries, views, seo, git, contents)
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function getCacheDirectories(&$config, $silent = false) {
		$cacheDirectory = self::initialGetCacheDirectory($config);
		$rootDS = \ROOT . \DS;
		if (! $silent) {
			echo "cache directory is " . UFileSystem::cleanPathname($rootDS . $cacheDirectory) . "\n";
		}
		$cacheDirectory = $rootDS . $cacheDirectory . \DS;
		$modelsDir = str_replace("\\", \DS, $config['mvcNS']['models']);
		$controllersDir = str_replace("\\", \DS, $config['mvcNS']['controllers']);
		$annotationCacheDir = $cacheDirectory . 'annotations';
		$modelsCacheDir = $cacheDirectory . $modelsDir;
		$queriesCacheDir = $cacheDirectory . 'queries';
		$controllersCacheDir = $cacheDirectory . $controllersDir;
		$viewsCacheDir = $cacheDirectory . 'views';
		$seoCacheDir = $cacheDirectory . 'seo';
		$gitCacheDir = $cacheDirectory . 'git';
		$contentsCacheDir = $cacheDirectory . 'contents';
		return [
			'annotations' => $annotationCacheDir,
			'models' => $modelsCacheDir,
			'controllers' => $controllersCacheDir,
			'queries' => $queriesCacheDir,
			'views' => $viewsCacheDir,
			'seo' => $seoCacheDir,
			'git' => $gitCacheDir,
			'contents' => $contentsCacheDir
		];
	}

	private static function safeMkdir($dir) {
		if (! \is_dir($dir))
			return \mkdir($dir, 0777, true);
	}

	/**
	 * Deletes files from a cache type
	 *
	 * @param array $config
	 * @param string $type
	 */
	public static function clearCache(&$config, $type = 'all') {
		$cacheDirectories = self::checkCache($config);
		$cacheDirs = [
			'annotations',
			'controllers',
			'models',
			'queries',
			'views',
			'contents'
		];
		foreach ($cacheDirs as $typeRef) {
			self::_clearCache($cacheDirectories, $type, $typeRef);
		}
	}

	private static function _clearCache($cacheDirectories, $type, $typeRef) {
		if ($type === 'all' || $type === $typeRef)
			UFileSystem::deleteAllFilesFromFolder($cacheDirectories[$typeRef]);
	}

	/**
	 *
	 * @param array $config
	 * @param string $type
	 * @param boolean $silent
	 */
	public static function initCache(&$config, $type = 'all', $silent = false) {
		self::checkCache($config, $silent);
		self::start($config);
		if ($type === 'all' || $type === 'models') {
			self::initModelsCache($config, false, $silent);
		}
		if ($type === 'all' || $type === 'controllers') {
			if (\class_exists('\\Ubiquity\\security\\acl\\AclManager')) {
				self::getAnnotationsEngineInstance()->registerAcls();
			}
			self::initRouterCache($config, $silent);
		}
		if ($type === 'all' || $type === 'acls') {
			if (\class_exists('\\Ubiquity\\security\\acl\\AclManager')) {
				\Ubiquity\security\acl\AclManager::initCache($config);
			}
		}
		if ($type === 'all' || $type === 'rest') {
			self::initRestCache($config, $silent);
		}
	}
}

