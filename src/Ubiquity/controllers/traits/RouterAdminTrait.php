<?php

namespace Ubiquity\controllers\traits;

/**
 * Trait for admin part of Router class.
 * Ubiquity\controllers\traits$RouterAdminTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 * @property array $routes
 */
trait RouterAdminTrait {

	abstract public static function slashPath($path): string;

	/**
	 *
	 * @param string $controller
	 * @param string $action
	 * @return array|boolean
	 */
	public static function getRouteInfoByControllerAction($controller, $action) {
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (! isset ( $routeDetails ['controller'] )) {
				$routeDetails = \current ( $routeDetails );
			}
			if ($controller === $routeDetails ['controller'] && $action === $routeDetails ['action']) {
				$routeDetails ['path'] = $routePath;
				return $routeDetails;
			}
		}
		return false;
	}

	public static function getRoutesPathByController($controller): array {
		$result = [ ];
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (! isset ( $routeDetails ['controller'] )) {
				foreach ( $routeDetails as $method => $routeInfo ) {
					if ($routeInfo ['controller'] === $controller) {
						$result [] = [ 'method' => $method,'url' => self::getRoutePathInfos ( $controller, $routePath, $routeInfo ) ];
					}
				}
			} else {
				if ($routeDetails ['controller'] === $controller) {
					$result [] = [ 'method' => '*','url' => self::getRoutePathInfos ( $controller, $routePath, $routeDetails ) ];
				}
			}
		}
		return $result;
	}

	public static function getRoutePathInfos($controller, $routePath, $routeInfo) {
		$method = new \ReflectionMethod ( $controller, $routeInfo ['action'] );
		$parameters = $method->getParameters ();
		$routeParams = $routeInfo ['parameters'];
		$pattern = "@\(.*?\)@";
		$params = [ ];
		foreach ( $routeParams as $param ) {
			if ($param === '*') {
				$params [] = $parameters [\sizeof ( $params )]->getName ();
			} else {
				$index = ( int ) \filter_var ( $param, FILTER_SANITIZE_NUMBER_INT );
				$params [] = $parameters [$index]->getName ();
			}
		}
		$path = $routePath;
		foreach ( $params as $param ) {
			$path = \preg_replace ( $pattern, '{' . $param . '}', $path, 1 );
		}
		return $path;
	}

	/**
	 *
	 * @param string $path
	 * @return array|boolean
	 */
	public static function getRouteInfo($path) {
		$path = self::slashPath ( $path );
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (\preg_match ( "@^{$routePath}\$@s", $path, $matches ) || \stripslashes ( $routePath ) == $path) {
				if (! isset ( $routeDetails ['controller'] )) {
					return \current ( $routeDetails );
				} else
					return $routeDetails;
			}
		}
		return false;
	}

	public static function getAnnotations($controllerName, $actionName): array {
		$result = [ ];
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (! isset ( $routeDetails ['controller'] )) {
				$routeDetails = \current ( $routeDetails );
			}
			if ($routeDetails ['controller'] === $controllerName && $routeDetails ['action'] === $actionName)
				$result [$routePath] = $routeDetails;
		}
		return $result;
	}

	public static function filterRoutes($path) {
		$path = self::slashPath ( $path );
		$result = [ ];
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (\preg_match ( "@^{$routePath}.*?$@s", $path, $matches )) {
				$result [$routePath] = $routeDetails;
			}
		}
		return $result;
	}
}

