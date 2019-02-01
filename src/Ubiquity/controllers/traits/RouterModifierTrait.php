<?php

namespace Ubiquity\controllers\traits;

use Ubiquity\cache\parser\ControllerParser;
use Ubiquity\cache\parser\CallableParser;

trait RouterModifierTrait {

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
	public static function addRoute($path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = [], $priority = 0) {
		self::addRouteToRoutes ( self::$routes, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements, $priority );
	}

	public static function addRouteToRoutes(&$routesArray, $path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = [], $priority = 0) {
		if (\class_exists ( $controller )) {
			$method = new \ReflectionMethod ( $controller, $action );
			self::_addRoute ( $method, $routesArray, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements, $priority );
		}
	}

	private static function _addRoute(\ReflectionMethod $method, &$routesArray, $path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = [], $priority = 0) {
		$result = [ ];
		ControllerParser::parseRouteArray ( $result, $controller, [ "path" => $path,"methods" => $methods,"name" => $name,"cache" => $cache,"duration" => $duration,"requirements" => $requirements,"priority" => $priority ], $method, $action );
		foreach ( $result as $k => $v ) {
			$routesArray [$k] = $v;
		}
	}

	public static function addCallableRoute($path, $callable, $methods = null, $name = "", $cache = false, $duration = null, $requirements = [], $priority = 0) {
		if (is_callable ( $callable )) {
			$reflectionFunction = new \ReflectionFunction ( $callable );
			self::_addCallableRoute ( $reflectionFunction, self::$routes, $path, $callable, $methods, $name, $cache, $duration, $requirements, $priority );
		}
	}

	public static function get($path, $callable, $name = "", $cache = false, $duration = null, $requirements = [], $priority = 0) {
		self::addCallableRoute ( $path, $callable, [ 'get' ], $name, $cache, $duration, $requirements, $priority );
	}

	private static function _addCallableRoute(\ReflectionFunction $reflectionFunction, &$routesArray, $path, $callable, $methods = null, $name = "", $cache = false, $duration = null, $requirements = [], $priority = 0) {
		$result = [ ];
		CallableParser::parseRouteArray ( $result, $callable, [ "path" => $path,"methods" => $methods,"name" => $name,"cache" => $cache,"duration" => $duration,"requirements" => $requirements,"priority" => $priority ], $reflectionFunction );
		foreach ( $result as $k => $v ) {
			$routesArray [$k] = $v;
		}
	}

	public static function addRoutesToRoutes(&$routesArray, $paths, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = [], $priority = 0) {
		if (\class_exists ( $controller )) {
			$method = new \ReflectionMethod ( $controller, $action );
			foreach ( $paths as $path ) {
				self::_addRoute ( $method, $routesArray, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements, $priority );
			}
		}
	}
}

