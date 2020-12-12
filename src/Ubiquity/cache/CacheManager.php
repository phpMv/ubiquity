<?php

/**
 * Cache managment
 */
namespace Ubiquity\cache;

use Ubiquity\cache\traits\ModelsCacheTrait;
use Ubiquity\cache\traits\RestCacheTrait;
use Ubiquity\cache\traits\RouterCacheTrait;
use Ubiquity\utils\base\UFileSystem;

use Ubiquity\controllers\Startup;
use Ubiquity\cache\traits\DevCacheTrait;
use Ubiquity\cache\traits\DevRouterCacheTrait;

/**
 * Manager for caches (Router, Rest, models).
 * Ubiquity\cache$CacheManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.6
 *
 */
class CacheManager {
	use DevCacheTrait,RouterCacheTrait,DevRouterCacheTrait,ModelsCacheTrait,RestCacheTrait;

	/**
	 *
	 * @var \Ubiquity\cache\system\AbstractDataCache
	 */
	public static $cache;

	private static $cacheDirectory;

	/**
	 * Starts the cache for production
	 *
	 * @param array $config
	 */
	public static function startProd(&$config) {
		self::$cacheDirectory = self::initialGetCacheDirectory($config);
		$cacheDirectory = \ROOT . \DS . self::$cacheDirectory;
		self::getCacheInstance($config, $cacheDirectory, '.cache');
	}

	/**
	 * Starts the cache from a controller
	 */
	public static function startProdFromCtrl() {
		$config = &Startup::$config;
		$cacheD = \ROOT . \DS . ($config['cache']['directory'] ??= 'cache' . \DS);
		$cacheSystem = $config['cache']['system'] ?? 'Ubiquity\\cache\\system\\ArrayCache';
		self::$cache = new $cacheSystem($cacheD, '.cache', $config['cache']['params'] ?? []);
	}

	protected static function getCacheInstance(&$config, $cacheDirectory, $postfix) {
		if (! isset(self::$cache)) {
			$cacheSystem = $config['cache']['system'] ?? 'Ubiquity\\cache\\system\\ArrayCache';
			$cacheParams = $config['cache']['params'] ?? [];

			self::$cache = new $cacheSystem($cacheDirectory, $postfix, $cacheParams);
		}
		return self::$cache;
	}


	/**
	 * Returns the relative cache directory
	 *
	 * @return string
	 */
	public static function getCacheDirectory() {
		return self::$cacheDirectory;
	}

	/**
	 * Returns the absolute cache directory
	 *
	 * @return string
	 */
	public static function getAbsoluteCacheDirectory() {
		return \ROOT . \DS . self::$cacheDirectory;
	}

	/**
	 * Returns an absolute cache subdirectory
	 *
	 * @param string $subDirectory
	 * @return string
	 */
	public static function getCacheSubDirectory($subDirectory) {
		return \ROOT . \DS . self::$cacheDirectory . \DS . $subDirectory;
	}


	/**
	 * Returns an array of all defined routes, included REST routes
	 *
	 * @return array
	 */
	public static function getAllRoutes() {
		$routes = self::getControllerCache();
		return \array_merge($routes, self::getControllerCache(true));
	}

	/**
	 * Returns an array of files from type $type
	 *
	 * @param array $config
	 * @param string $type
	 * @param boolean $silent
	 * @return array
	 */
	protected static function _getFiles(&$config, $type, $silent = false) {
		$typeNS = $config['mvcNS'][$type];
		$typeDir = \ROOT . \DS . \str_replace("\\", \DS, $typeNS);
		if (! $silent)
			echo \ucfirst($type) . ' directory is ' . \ROOT . $typeNS . "\n";
		return UFileSystem::glob_recursive($typeDir . \DS . '*.php');
	}
}
