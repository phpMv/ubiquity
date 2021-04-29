<?php

namespace Ubiquity\controllers\di;

use Ubiquity\cache\CacheManager;

/**
 * Manage dependency injection.
 * Ubiquity\controllers\di$DiManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 * @since Ubiquity 2.1.0
 *
 */
class DiManager {
	protected static $key = "controllers/di/";

	/**
	 * Initialize dependency injection cache
	 * To use in dev only!
	 *
	 * @param array $config
	 */
	public static function init(&$config) {
		$controllers = CacheManager::getControllers ();
		foreach ( $controllers as $controller ) {
			CacheManager::$cache->remove ( self::getControllerCacheKey ( $controller ) );
			$parser = new DiControllerParser ();
			$parser->parse ( $controller, $config );
			$injections = $parser->getInjections ();
			if (\count ( $injections ) > 0) {
				self::store ( $controller, $injections );
			}
		}
	}

	protected static function store($controller, $injections) {
		CacheManager::$cache->store ( self::getControllerCacheKey ( $controller ), $injections );
	}

	public static function fetch($controller) {
		$key = self::getControllerCacheKey ( $controller );
		if (CacheManager::$cache->exists ( $key )) {
			return CacheManager::$cache->fetch ( $key );
		}
		return false;
	}

	protected static function getControllerCacheKey($classname) {
		if (\is_object ( $classname )) {
			$classname = \get_class ( $classname );
		}
		return self::$key . \str_replace ( "\\", \DS, $classname );
	}
}

