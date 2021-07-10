<?php

namespace Ubiquity\controllers;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\traits\RouterAdminTrait;
use Ubiquity\controllers\traits\RouterModifierTrait;
use Ubiquity\controllers\traits\RouterTestTrait;
use Ubiquity\log\Logger;
use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\router\RouterStatus;

/**
 * Router manager.
 * Ubiquity\controllers$Router
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.0
 *
 */
class Router {
	use RouterModifierTrait,RouterAdminTrait,RouterTestTrait;
	protected static $routes;
	protected static $statusCode;

	private static function cleanParam(string $param): string {
		if (\substr ( $param, - 1 ) === '/') {
			return \substr ( $param, 0, - 1 );
		}
		return $param;
	}

	private static function getRoute_(&$routeDetails, $routePath, $matches, $cachedResponse) {
		self::$statusCode=RouterStatus::OK;
		if (! isset ( $routeDetails ['controller'] )) {
			$method = \strtolower ( $_SERVER ['REQUEST_METHOD'] );
			if (isset ( $routeDetails [$method] )) {
				$routeDetailsMethod = $routeDetails [$method];
				return self::getRouteUrlParts ( [ 'path' => $routePath,'details' => $routeDetailsMethod ], $matches, $routeDetailsMethod ['cache'] ?? false, $routeDetailsMethod ['duration'] ?? null, $cachedResponse );
			}
			self::$statusCode=RouterStatus::METHOD_NOT_ALLOWED;
		} else {
			return self::getRouteUrlParts ( [ 'path' => $routePath,'details' => $routeDetails ], $matches, $routeDetails ['cache'] ?? false, $routeDetails ['duration'] ?? null, $cachedResponse );
		}
		if(self::$statusCode===RouterStatus::OK) {
			self::$statusCode = RouterStatus::NOT_FOUND;
		}
		return false;
	}

	protected static function _getURL($routePath, $params) {
		$result = \preg_replace_callback ( '~\((.*?)\)~', function () use (&$params) {
			return \array_shift ( $params );
		}, $routePath );
		if (\count ( $params ) > 0) {
			$result = \rtrim ( $result, '/' ) . '/' . \implode ( '/', $params );
		}
		return $result;
	}

	protected static function checkRouteName($routeDetails, $name) {
		if (! isset ( $routeDetails ['name'] )) {
			foreach ( $routeDetails as $methodRouteDetail ) {
				if (isset ( $methodRouteDetail ['name'] ) && $methodRouteDetail ['name'] == $name) {
					return true;
				}
			}
		}
		return isset ( $routeDetails ['name'] ) && $routeDetails ['name'] == $name;
	}

	protected static function setParamsInOrder($paramsOrder, $params) {
		$index = 0;
		$newParams=[];
		foreach ( $paramsOrder as $order ) {
			if ($order === '*') {
				if (isset ( $params [$index] )) {
					$newParams = \array_merge ( $newParams, \array_diff ( \explode ( '/', $params [$index] ), [ '' ] ) );
				}
				break;
			}
			if (($order [0] ?? '') === '~') {
				$order = \intval ( \substr ( $order, 1, 1 ) );
				if (isset ( $params [$order] )) {
					$newParams = \array_merge ( $newParams, \array_diff ( \explode ( '/', $params [$order] ), [ '' ] ) );
					break;
				}
			}
			$newParams [] = self::cleanParam ( $params [$order] );
			unset ( $params [$order] );
			$index ++;
		}
		return $newParams;
	}

	/**
	 * Starts the router by loading normal routes (not rest)
	 */
	public static function start(): void {
		self::$routes = CacheManager::getControllerCache ();
	}

	/**
	 * Starts the router by loading rest routes (not normal routes)
	 */
	public static function startRest(): void {
		self::$routes = CacheManager::getControllerCache ( true );
	}

	/**
	 * Starts the router by loading all routes (normal + rest routes)
	 */
	public static function startAll(): void {
		self::$routes = \array_merge ( CacheManager::getControllerCache (), CacheManager::getControllerCache ( true ) );
	}

	/**
	 * Returns the route corresponding to a path
	 *
	 * @param string $path The route path
	 * @param boolean $cachedResponse
	 * @return boolean|mixed[]|string
	 */
	public static function getRoute($path, $cachedResponse = true, $debug = false) {
		$path = self::slashPath ( $path );
		if (isset ( self::$routes [$path] ) && ! $debug) { // No direct access to route in debug mode (for maintenance mode activation)
			return self::getRoute_ ( self::$routes [$path], $path, [ $path ], $cachedResponse );
		}
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (\preg_match ( "@^{$routePath}\$@s", $path, $matches )) {
				if (($r = self::getRoute_ ( $routeDetails, $routePath, $matches, $cachedResponse )) !== false) {
					return $r;
				}
			}
		}
		return false;
	}

	/**
	 * Returns the generated path from a route
	 *
	 * @param string $name name of the route
	 * @param array $parameters array of the route parameters. default : []
	 * @param boolean $absolute
	 */
	public static function getRouteByName($name, $parameters = [], $absolute = true) {
		foreach ( self::$routes as $routePath => $routeDetails ) {
			if (self::checkRouteName ( $routeDetails, $name )) {
				if (\trim ( $routePath, '/' ) == '_default') {
					return ($absolute)?'/':'';
				}
				if (\count ( $parameters ) > 0) {
					$routePath = self::_getURL ( $routePath, $parameters );
				}
				$routePath = \str_replace('//', '/',\preg_replace('~\((.*?)\)~', '', $routePath));
				return ($absolute)?$routePath:\ltrim ( $routePath, '/' );
			}
		}
		return false;
	}

	public static function getRouteInfoByName($name) {
		foreach ( self::$routes as $routeDetails ) {
			if (self::checkRouteName ( $routeDetails, $name )) {
				return $routeDetails;
			}
		}
		return false;
	}

	/**
	 * Returns the generated path from a route
	 *
	 * @param string $name The route name
	 * @param array $parameters default: []
	 * @param boolean $absolute true if the path is absolute (/ at first)
	 * @return boolean|string|array|mixed the generated path (/path/to/route)
	 */
	public static function path($name, $parameters = [], $absolute = false) {
		return self::getRouteByName ( $name, $parameters, $absolute );
	}

	/**
	 * Returns the generated url from a route
	 *
	 * @param string $name the route name
	 * @param array $parameters default: []
	 * @return string the generated url (http://myApp/path/to/route)
	 */
	public static function url($name, $parameters = []): string {
		return URequest::getUrl ( self::getRouteByName ( $name, $parameters, false ) );
	}

	public static function getRouteUrlParts($routeArray, $params, $cached = false, $duration = NULL, $cachedResponse = true) {
		$realPath = \current ( $params );
		\array_shift ( $params );
		$routeDetails = $routeArray ['details'];
		if ($routeDetails ['controller'] instanceof \Closure) {
			$result = [ 'callback'=>$routeDetails ['controller'] ];
			$resultStr = 'callable function';
		} else {
			$mainParams=null;
			if(($mainMethodParams=$routeDetails['main.params']??null)!==null){
				foreach ($mainMethodParams as $index=>$mainMethodParam) {
					$mainParams[$mainMethodParam]=$params[$index];
				}
				$params=\array_slice ( $params, $index+1);
			}
			$result = [ 'controller'=>\str_replace ( "\\\\", "\\", $routeDetails ['controller'] ),'action'=>$routeDetails ['action'],'mainParams'=>$mainParams];
			$resultStr = \json_encode($result);
		}
		if (($paramsOrder = $routeDetails ['parameters']) && (\count ( $paramsOrder ) > 0)) {
			$result['params']=self::setParamsInOrder ( $paramsOrder, $params );
		}
		if (! $cached || ! $cachedResponse) {
			Logger::info ( 'Router', \sprintf ( 'Route found for %s : %s', $routeArray ['path'], $resultStr ), 'getRouteUrlParts' );
			if (isset ( $routeDetails ['callback'] )) {
				// Used for maintenance mode
				if ($routeDetails ['callback'] instanceof \Closure) {
					return $routeDetails ['callback'] ( $result );
				}
			}
			return $result;
		}
		Logger::info ( 'Router', sprintf ( 'Route found for %s (from cache) : %s', $realPath, $resultStr ), 'getRouteUrlParts' );
		return CacheManager::getRouteCache ( $realPath, $result, $duration );
	}

	/**
	 * Adds a slash before and after a path
	 *
	 * @param string $path The path to modify
	 * @return string The path with slashes
	 */
	public static function slashPath($path): string {
		if (\substr ( $path, 0, 1 ) !== '/') {
			$path = '/' . $path;
		}
		if (\substr ( $path, - 1 ) !== '/') {
			$path = $path . '/';
		}
		return $path;
	}

	/**
	 * Declare a route as expired
	 *
	 * @param string $routePath
	 */
	public static function setExpired($routePath): void {
		CacheManager::setExpired ( $routePath );
	}

	/**
	 * Returns the array of loaded routes
	 *
	 * @return array|mixed
	 */
	public static function getRoutes() {
		return self::$routes;
	}
	
	/**
	 * Return router response status code.
	 * @return int
	 * @since 2.4.5
	 */
	public static function getStatusCode():int{
		return self::$statusCode;
	}
}
