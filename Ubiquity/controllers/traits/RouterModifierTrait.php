<?php

namespace Ubiquity\controllers\traits;

use Ubiquity\cache\parser\ControllerParser;

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
	public static function addRoute($path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = []) {
		self::addRouteToRoutes ( self::$routes, $path, $controller, $action, $methods, $name, $cache, $duration, $requirements );
	}
	
	public static function addRouteToRoutes(&$routesArray, $path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = []) {
		if (\class_exists ( $controller )) {
			$method = new \ReflectionMethod ( $controller, $action );
			self::_addRoute($method, $routesArray, $path, $controller,$action,$methods,$name,$cache,$duration,$requirements);
		}
	}
	
	private static function _addRoute($method,&$routesArray, $path, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = []){
		$result=[];
		ControllerParser::parseRouteArray ( $result, $controller, [ "path" => $path,"methods" => $methods,"name" => $name,"cache" => $cache,"duration" => $duration,"requirements" => $requirements ], $method, $action );
		foreach ( $result as $k => $v ) {
			$routesArray [$k] = $v;
		}
	}
	
	public static function addRoutesToRoutes(&$routesArray, $paths, $controller, $action = "index", $methods = null, $name = "", $cache = false, $duration = null, $requirements = []) {
		if (\class_exists ( $controller )) {
			$method = new \ReflectionMethod ( $controller, $action );
			foreach ($paths as $path){
				self::_addRoute($method, $routesArray, $path, $controller,$action,$methods,$name,$cache,$duration,$requirements);
			}
		}
	}
}

