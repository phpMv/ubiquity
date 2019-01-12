<?php

namespace Ubiquity\controllers\traits;

/**
 * @author jcheron <myaddressmail@gmail.com>
 * @property array $routes
 *
 */
trait RouterAdminTrait {
	
	abstract protected static function slashPath($path);
	
	public static function getRouteInfoByControllerAction($controller, $action) {
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (! isset ( $routeDetails ["controller"] )) {
				$routeDetails = \current ( $routeDetails );
			}
			if ($controller === $routeDetails ["controller"] && $action === $routeDetails ["action"]) {
				$routeDetails ["path"] = $routePath;
				return $routeDetails;
			}
		}
		return false;
	}
	
	public static function getRouteInfo($path) {
		$path = self::slashPath ( $path );
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (preg_match ( "@^" . $routePath . "$@s", $path, $matches ) || \stripslashes ( $routePath ) == $path) {
				if (! isset ( $routeDetails ["controller"] )) {
					return \current ( $routeDetails );
				} else
					return $routeDetails;
			}
		}
		return false;
	}
	
	public static function getAnnotations($controllerName, $actionName) {
		$result = [ ];
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (! isset ( $routeDetails ["controller"] )) {
				$routeDetails = \current ( $routeDetails );
			}
			if ($routeDetails ["controller"] === $controllerName && $routeDetails ["action"] === $actionName)
				$result [$routePath] = $routeDetails;
		}
		return $result;
	}
	
	public static function filterRoutes($path) {
		$path = self::slashPath ( $path );
		$result = [ ];
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (preg_match ( "@^" . $routePath . ".*?$@s", $path, $matches )) {
				$result [$routePath] = $routeDetails;
			}
		}
		return $result;
	}
}

