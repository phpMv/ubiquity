<?php

namespace Ubiquity\controllers\traits;

use Ubiquity\utils\base\UString;

/**
 * Ubiquity\controllers\traits$RouterTestTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 * @property array $routes
 *
 */
trait RouterTestTrait {

	public static function testRoutes($path, $method = null): array {
		$response = [ ];
		if (isset ( $path )) {
			$path = self::slashPath ( $path );
			if (isset ( self::$routes [$path] )) {
				self::addTestRoute ( $response, $path, $method );
			}
		}
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (\preg_match ( "@^{$routePath}\$@s", $path ) || $path == null) {
				self::addTestRoute ( $response, $routePath, $method );
			}
		}
		return $response;
	}

	private static function addTestRoute(&$response, $path, $method = null): void {
		if (isset ( $method )) {
			$restrict = false;
			if (UString::startswith ( $method, '-' )) {
				$restrict = true;
				$method = \ltrim ( $method, '-' );
			}
			$routeMethod = self::getMethod ( self::$routes [$path] );
			if ((\count ( $routeMethod ) == 0 && ! $restrict) || \array_search ( \strtolower ( $method ), $routeMethod ) !== false) {
				$response [$path] = self::$routes [$path];
			}
		} else {
			$response [$path] = self::$routes [$path];
		}
	}

	private static function getMethod($routeDetails): array {
		if (! isset ( $routeDetails ['controller'] )) {
			return \array_keys ( $routeDetails );
		}
		return [ ];
	}
}

