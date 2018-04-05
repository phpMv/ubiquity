<?php

namespace Ubiquity\controllers;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\http\URequest;
use Ubiquity\cache\parser\ControllerParser;
use Ubiquity\utils\base\UString;

/**
 * Router
 *
 * @author jc
 * @version 1.0.0.2
 */
class Router {
	private static $routes;

	public static function slashPath($path) {
		if (UString::startswith ( $path, "/" ) === false)
			$path = "/" . $path;
		if (! UString::endswith ( $path, "/" ))
			$path = $path . "/";
		return $path;
	}

	public static function start() {
		self::$routes = CacheManager::getControllerCache ();
	}

	public static function startRest() {
		self::$routes = CacheManager::getControllerCache ( true );
	}

	public static function getRoute($path, $cachedResponse = true) {
		$path = self::slashPath ( $path );
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (preg_match ( "@^" . $routePath . "$@s", $path, $matches )) {
				if (! isset ( $routeDetails ["controller"] )) {
					$method = URequest::getMethod ();
					if (isset ( $routeDetails [$method] ))
						return self::getRouteUrlParts ( [ "path" => $routePath,"details" => $routeDetails [$method] ], $matches, $routeDetails [$method] ["cache"], $routeDetails [$method] ["duration"], $cachedResponse );
				} else
					return self::getRouteUrlParts ( [ "path" => $routePath,"details" => $routeDetails ], $matches, $routeDetails ["cache"], $routeDetails ["duration"], $cachedResponse );
			}
		}
		return false;
	}

	public static function getRouteInfoByControllerAction($controller, $action) {
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (! isset ( $routeDetails ["controller"] )) {
				$routeDetails = \reset ( $routeDetails );
			}
			if ($controller === $routeDetails ["controller"] && $action === $routeDetails ["action"]) {
				$routeDetails ["path"] = $routePath;
				return $routeDetails;
			}
		}
		return false;
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

	public static function getRouteInfo($path) {
		$path = self::slashPath ( $path );
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (preg_match ( "@^" . $routePath . "$@s", $path, $matches ) || \stripslashes ( $routePath ) == $path) {
				if (! isset ( $routeDetails ["controller"] )) {
					return \reset ( $routeDetails );
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
				$routeDetails = \reset ( $routeDetails );
			}
			if ($routeDetails ["controller"] === $controllerName && $routeDetails ["action"] === $actionName)
				$result [$routePath] = $routeDetails;
		}
		return $result;
	}

	/**
	 * Returns the generated path from a route
	 *
	 * @param string $name
	 *        	name of the route
	 * @param array $parameters
	 *        	array of the route parameters. default : []
	 * @param boolean $absolute
	 */
	public static function getRouteByName($name, $parameters = [], $absolute = true) {
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (self::checkRouteName ( $routeDetails, $name )) {
				if (\sizeof ( $parameters ) > 0)
					$routePath = self::_getURL ( $routePath, $parameters );
				if (! $absolute)
					return \ltrim ( $routePath, '/' );
				else
					return $routePath;
			}
		}
		return false;
	}

	/**
	 * Returns the generated path from a route
	 *
	 * @param string $name
	 *        	the route name
	 * @param array $parameters
	 *        	default: []
	 * @param boolean $absolute
	 *        	true if the path is absolute (/ at first)
	 * @return boolean|string|array|mixed the generated path (/path/to/route)
	 */
	public static function path($name, $parameters = [], $absolute = false) {
		return self::getRouteByName ( $name, $parameters, $absolute );
	}

	/**
	 * Returns the generated url from a route
	 *
	 * @param string $name
	 *        	the route name
	 * @param array $parameters
	 *        	default: []
	 * @return string the generated url (http://myApp/path/to/route)
	 */
	public static function url($name, $parameters = []) {
		return URequest::getUrl ( self::getRouteByName ( $name, $parameters, false ) );
	}

	protected static function _getURL($routePath, $params) {
		$result = \preg_replace_callback ( '~\((.*?)\)~', function () use (&$params) {
			return array_shift ( $params );
		}, $routePath );
		if (\sizeof ( $params ) > 0) {
			$result = \rtrim ( $result, '/' ) . '/' . \implode ( '/', $params );
		}
		return $result;
	}

	protected static function checkRouteName($routeDetails, $name) {
		if (! isset ( $routeDetails ["name"] )) {
			foreach ( $routeDetails as $methodRouteDetail ) {
				if (isset ( $methodRouteDetail ["name"] ) && $methodRouteDetail == $name)
					return true;
			}
		}
		return isset ( $routeDetails ["name"] ) && $routeDetails ["name"] == $name;
	}

	public static function getRouteUrlParts($routeArray, $params, $cached = false, $duration = NULL, $cachedResponse = true) {
		$params = \array_slice ( $params, 1 );
		$ctrl = str_replace ( "\\\\", "\\", $routeArray ["details"] ["controller"] );
		$result = [ $ctrl,$routeArray ["details"] ["action"] ];
		$paramsOrder = $routeArray ["details"] ["parameters"];
		$index = 0;
		foreach ( $paramsOrder as $order ) {
			if ($order === "*") {
				if (isset ( $params [$index] ))
					$result = \array_merge ( $result, \array_diff ( \explode ( "/", $params [$index] ), [ "" ] ) );
				break;
			}
			if (\substr ( $order, 0, 1 ) === "~") {
				$order = \intval ( \substr ( $order, 1, 1 ) );
				if (isset ( $params [$order] )) {
					$result = \array_merge ( $result, \array_diff ( \explode ( "/", $params [$order] ), [ "" ] ) );
					break;
				}
			}
			$result [] = self::cleanParam ( $params [$order] );
			unset ( $params [$order] );
			$index ++;
		}
		if ($cached === true && $cachedResponse === true) {
			return CacheManager::getRouteCache ( $result, $duration );
		}
		return $result;
	}

	private static function cleanParam($param) {
		if (UString::endswith ( $param, "/" ))
			return \substr ( $param, 0, - 1 );
		return $param;
	}

	/**
	 * Déclare une route comme étant expirée ou non
	 *
	 * @param string $routePath
	 * @param boolean $expired
	 */
	public static function setExpired($routePath, $expired = true) {
		CacheManager::setExpired ( $routePath, $expired );
	}

	/**
	 *
	 * @param string $path
	 * @param string $controller
	 * @param string $action
	 * @param array|null $methods
	 * @param string $name
	 * @param boolean $cache
	 * @param int $duration
	 * @param array $requirements
	 */
	public static function addRoute($path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = []) {
		self::addRouteToRoutes ( self::$routes, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements );
	}

	public static function addRouteToRoutes(&$routesArray, $path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = []) {
		$result = [ ];
		if (\class_exists ( $controller )) {
			$method = new \ReflectionMethod ( $controller, $action );
			ControllerParser::parseRouteArray ( $result, $controller, [ "path" => $path,"methods" => $methods,"name" => $name,"cache" => $cache,"duration" => $duration,"requirements" => $requirements ], $method, $action );
			foreach ( $result as $k => $v ) {
				$routesArray [$k] = $v;
			}
		}
	}
}
