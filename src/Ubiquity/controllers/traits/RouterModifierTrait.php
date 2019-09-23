<?php

namespace Ubiquity\controllers\traits;

use Ubiquity\cache\parser\ControllerParser;
use Ubiquity\cache\parser\CallableParser;

/**
 * Ubiquity\controllers\traits$RouterModifierTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 *
 */
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
	public static function addRoute($path, $controller, $action = 'index', $methods = null, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		self::addRouteToRoutes ( self::$routes, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements, $priority );
	}

	public static function addRouteToRoutes(&$routesArray, $path, $controller, $action = 'index', $methods = null, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0, $callback = null): void {
		if (\class_exists ( $controller )) {
			$method = new \ReflectionMethod ( $controller, $action );
			$path = ControllerParser::parseMethodPath ( $method, $path );
			self::_addRoute ( $method, $routesArray, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements, $priority, $callback );
		}
	}

	private static function _addRoute(\ReflectionMethod $method, &$routesArray, $path, $controller, $action = 'index', $methods = null, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0, $callback = null): void {
		$result = [ ];
		ControllerParser::parseRouteArray ( $result, $controller, [ 'path' => $path,'methods' => $methods,'name' => $name,'cache' => $cache,'duration' => $duration,'requirements' => $requirements,'priority' => $priority,'callback' => $callback ], $method, $action );
		if ($priority <= 0) {
			foreach ( $result as $k => $v ) {
				$routesArray [$k] = $v;
			}
		} else {
			$count = \count ( $routesArray );
			$newArray = [ ];
			foreach ( $routesArray as $k => $v ) {
				if ($priority < $count --) {
					$newArray [$k] = $v;
				} else {
					break;
				}
			}
			$routesArray = \array_diff_key ( $routesArray, $newArray );
			foreach ( $result as $k => $v ) {
				$newArray [$k] = $v;
			}
			foreach ( $routesArray as $k => $v ) {
				$newArray [$k] = $v;
			}
			$routesArray = $newArray;
		}
	}

	public static function addCallableRoute($path, $callable, $methods = null, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		if ($callable instanceof \Closure) {
			$reflectionFunction = new \ReflectionFunction ( $callable );
			$path = ControllerParser::parseMethodPath ( $reflectionFunction, $path );
			self::_addCallableRoute ( $reflectionFunction, self::$routes, $path, $callable, $methods, $name, $cache, $duration, $requirements, $priority );
		}
	}

	public static function get($path, $callable, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		self::addCallableRoute ( $path, $callable, [ 'get' ], $name, $cache, $duration, $requirements, $priority );
	}

	public static function post($path, $callable, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		self::addCallableRoute ( $path, $callable, [ 'post' ], $name, $cache, $duration, $requirements, $priority );
	}

	public static function delete($path, $callable, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		self::addCallableRoute ( $path, $callable, [ 'delete' ], $name, $cache, $duration, $requirements, $priority );
	}

	public static function put($path, $callable, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		self::addCallableRoute ( $path, $callable, [ 'put' ], $name, $cache, $duration, $requirements, $priority );
	}

	public static function patch($path, $callable, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		self::addCallableRoute ( $path, $callable, [ 'patch' ], $name, $cache, $duration, $requirements, $priority );
	}

	public static function options($path, $callable, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		self::addCallableRoute ( $path, $callable, [ 'options' ], $name, $cache, $duration, $requirements, $priority );
	}

	private static function _addCallableRoute(\ReflectionFunction $reflectionFunction, &$routesArray, $path, $callable, $methods = null, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		$result = [ ];
		CallableParser::parseRouteArray ( $result, $callable, [ 'path' => $path,'methods' => $methods,'name' => $name,'cache' => $cache,'duration' => $duration,'requirements' => $requirements,'priority' => $priority ], $reflectionFunction );
		foreach ( $result as $k => $v ) {
			$routesArray [$k] = $v;
		}
	}

	public static function addRoutesToRoutes(&$routesArray, $paths, $controller, $action = 'index', $methods = null, $name = '', $cache = false, $duration = null, $requirements = [], $priority = 0): void {
		if (\class_exists ( $controller )) {
			$method = new \ReflectionMethod ( $controller, $action );
			foreach ( $paths as $path ) {
				self::_addRoute ( $method, $routesArray, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements, $priority );
			}
		}
	}
}

