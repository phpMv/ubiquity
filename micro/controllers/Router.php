<?php

namespace micro\controllers;

use micro\cache\CacheManager;
use micro\utils\RequestUtils;
use micro\cache\ControllerParser;
use micro\utils\StrUtils;

/**
 * Router
 * @author jc
 * @version 1.0.0.2
 */
class Router {
	private static $routes;

	private static function slashPath($path){
		if(StrUtils::startswith($path,"/")===false)
			$path="/" . $path;
		return $path;
	}

	public static function start() {
		self::$routes=CacheManager::getControllerCache();
	}

	public static function getRoute($path,$cachedResponse=true) {
		$path=self::slashPath($path);
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (preg_match("@^" . $routePath . "$@s", $path, $matches)) {
				if (!isset($routeDetails["controller"])) {
					$method=RequestUtils::getMethod();
					if (isset($routeDetails[$method]))
						return self::getRouteUrlParts([ "path" => $routePath,"details" => $routeDetails[$method] ], $matches, $routeDetails[$method]["cache"], $routeDetails[$method]["duration"],$cachedResponse);
				} else
					return self::getRouteUrlParts([ "path" => $routePath,"details" => $routeDetails ], $matches, $routeDetails["cache"], $routeDetails["duration"],$cachedResponse);
			}
		}
		return false;
	}

	public static function filterRoutes($path) {
		$path=self::slashPath($path);
		$result=[];
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (preg_match("@^" . $routePath . ".*?$@s", $path, $matches)) {
				$result[$routePath]=$routeDetails;
			}
		}
		return $result;
	}

	public static function getRouteInfo($path){
		$path=self::slashPath($path);
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (preg_match("@^" . $routePath . "$@s", $path, $matches)) {
				if (!isset($routeDetails["controller"])) {
						return \reset($routeDetails);
				} else
					return $routeDetails;
			}
		}
		return false;
	}

	public static function getAnnotations($controllerName,$actionName){
		$result=[];
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (!isset($routeDetails["controller"])) {
				$routeDetails=\reset($routeDetails);
			}
			if($routeDetails["controller"]===$controllerName && $routeDetails["action"]===$actionName)
				$result[$routePath]=$routeDetails;
		}
		return $result;
	}

	/**
	 * Retourne le chemin d'une route par son nom
	 * @param string $name nom de la route
	 */
	public static function getRouteByName($name, $absolute=true) {
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if ($routeDetails["name"] == $name) {
				if ($absolute)
					return RequestUtils::getUrl($routePath);
				else
					return $routePath;
			}
		}
		return false;
	}

	public static function getRouteUrlParts($routeArray, $params, $cached=false, $duration=NULL,$cachedResponse=true) {
		$params=\array_slice($params, 1);
		$ctrl=str_replace("\\\\", "\\", $routeArray["details"]["controller"]);
		$result=[ $ctrl,$routeArray["details"]["action"] ];
		$paramsOrder=$routeArray["details"]["parameters"];
		foreach ( $paramsOrder as $order ) {
			$result[]=$params[$order];
		}
		if ($cached === true && $cachedResponse===true) {
			return CacheManager::getRouteCache($result, $duration);
		}
		return $result;
	}

	/**
	 * Déclare une route comme étant expirée ou non
	 * @param string $routePath
	 * @param boolean $expired
	 */
	public static function setExpired($routePath, $expired=true) {
		CacheManager::setExpired($routePath, $expired);
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
	 */
	public static function addRoute($path, $controller, $action="index", $methods=null, $name="", $cache=false, $duration=null) {
		self::addRouteToRoutes(self::$routes, $path, $controller, $action, $methods, $name, $cache, $duration);
	}

	public static function addRouteToRoutes(&$routesArray, $path, $controller, $action="index", $methods=null, $name="", $cache=false, $duration=null) {
		$result=[ ];
		$method=new \ReflectionMethod($controller, $action);
		ControllerParser::parseRouteArray($result, $controller, [ "path" => $path,"methods" => $methods,"name" => $name,"cache" => $cache,"duration" => $duration ], $method, $action);
		foreach ( $result as $k => $v ) {
			$routesArray[$k]=$v;
		}
	}
}
