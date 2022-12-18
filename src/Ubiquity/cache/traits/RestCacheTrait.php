<?php

namespace Ubiquity\cache\traits;

use Ubiquity\cache\ClassUtils;
use Ubiquity\cache\parser\RestControllerParser;
use Ubiquity\utils\base\UArray;
use Ubiquity\exceptions\RestException;

/**
 *
 * Ubiquity\cache\traits$RestCacheTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 * @property \Ubiquity\cache\system\AbstractDataCache $cache
 */
trait RestCacheTrait {

	protected  static function initRestCache(array &$config, bool $silent = false): void {
		$restCache = [ ];
		$files = self::getControllersFiles ( $config );
		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$controller = ClassUtils::getClassFullNameFromFile ( $file );
				$parser = new RestControllerParser ();
				$parser->parse ( $controller, $config );
				if ($parser->isRest ()) {
					$restCache = \array_merge($restCache, $parser->asArray());
				}
			}
		}
		self::$cache->store ( 'controllers/rest', $restCache, 'controllers' );
		if (! $silent) {
			echo "Rest cache reset\n";
		}
	}

	public static function getRestRoutes(): array {
		$result = [ ];
		$restCache = self::getRestCache ();
		foreach ( $restCache as $controllerClass => $restAttributes ) {
			if (isset ( $restCache [$controllerClass] )) {
				$result [$controllerClass] = [ 'restAttributes' => $restAttributes,'routes' => self::getControllerRoutes ( $controllerClass, true ) ];
			}
		}
		return $result;
	}

	public static function getRestCache() {
		if (self::$cache->exists ( 'controllers/rest' )) {
			return self::$cache->fetch('controllers/rest');
		}
		throw new RestException ( 'Rest cache entry `' . self::$cache->getEntryKey ( 'controllers/rest' ) . "` is missing.\nTry to Re-init Rest cache." );
	}

	public static function getRestResource(string $controllerClass) {
		$cacheControllerClass = self::getRestCacheController ( $controllerClass );
		if (isset ( $cacheControllerClass )) {
			return $cacheControllerClass ['resource'];
		}
		return null;
	}

	public static function getRestCacheController(string $controllerClass) {
		$cache = self::getRestCache ();
		if (isset ( $cache [$controllerClass] )) {
			return $cache [$controllerClass];
		}
		return null;
	}
}
