<?php

namespace Ubiquity\cache\traits;

use Ubiquity\controllers\Startup;
use Ubiquity\controllers\Router;
use Ubiquity\cache\parser\ControllerParser;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UArray;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\di\DiManager;
use Ubiquity\utils\base\UIntrospection;
use Ubiquity\controllers\Controller;
use Ubiquity\controllers\StartupAsync;

/**
 *
 * Ubiquity\cache\traits$RouterCacheTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.8
 * @property \Ubiquity\cache\system\AbstractDataCache $cache
 *
 */
trait RouterCacheTrait {

	abstract protected static function _getFiles(&$config, $type, $silent = false);

	private static function addControllerCache($classname) {
		$parser = new ControllerParser ();
		try {
			$parser->parse ( $classname );
			return $parser->asArray ();
		} catch ( \Exception $e ) {
			// Nothing to do
		}
		return [ ];
	}

	private static function parseControllerFiles(&$config, $silent = false) {
		$routes = [ 'rest' => [ ],'default' => [ ] ];
		$files = self::getControllersFiles ( $config, $silent );
		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$controller = ClassUtils::getClassFullNameFromFile ( $file );
				$parser = new ControllerParser ();
				try {
					$parser->parse ( $controller );
					$ret = $parser->asArray ();
					$key = ($parser->isRest ()) ? 'rest' : 'default';
					$routes [$key] = \array_merge ( $routes [$key], $ret );
				} catch ( \Exception $e ) {
					// Nothing to do
				}
			}
		}
		self::sortByPriority ( $routes ['default'] );
		self::sortByPriority ( $routes ['rest'] );
		return $routes;
	}

	protected static function sortByPriority(&$array) {
		uasort ( $array, function ($item1, $item2) {
			return UArray::getRecursive ( $item2, 'priority', 0 ) <=> UArray::getRecursive ( $item1, 'priority', 0 );
		} );
		UArray::removeRecursive ( $array, 'priority' );
	}

	private static function initRouterCache(&$config, $silent = false) {
		$routes = self::parseControllerFiles ( $config, $silent );
		self::$cache->store ( 'controllers/routes.default', 'return ' . UArray::asPhpArray ( $routes ['default'], 'array' ) . ';', 'controllers' );
		self::$cache->store ( 'controllers/routes.rest', 'return ' . UArray::asPhpArray ( $routes ['rest'], 'array' ) . ';', 'controllers' );
		DiManager::init ( $config );
		if (! $silent) {
			echo "Router cache reset\n";
		}
	}

	public static function controllerCacheUpdated(&$config) {
		$result = false;
		$newRoutes = self::parseControllerFiles ( $config, true );
		$ctrls = self::getControllerCache ();
		if ($newRoutes ['default'] != $ctrls) {
			$result ['default'] = true;
		}
		$ctrls = self::getControllerCache ( true );
		if ($newRoutes ['rest'] != $ctrls) {
			$result ['rest'] = true;
		}
		return $result;
	}

	public static function storeDynamicRoutes($isRest = false) {
		$routes = Router::getRoutes ();
		$part = ($isRest) ? 'rest' : 'default';
		self::$cache->store ( 'controllers/routes.' . $part, 'return ' . UArray::asPhpArray ( $routes, 'array' ) . ';', 'controllers' );
	}

	private static function storeRouteResponse($key, $response) {
		self::$cache->store ( 'controllers/' . $key, $response, 'controllers', false );
		return $response;
	}

	private static function getRouteKey($routePath) {
		if (is_array ( $routePath )) {
			return 'path' . \md5 ( \implode ( '', $routePath ) );
		}
		return 'path' . \md5 ( Router::slashPath ( $routePath ) );
	}

	/**
	 *
	 * @param boolean $isRest
	 * @return array
	 */
	public static function getControllerCache($isRest = false) {
		$key = ($isRest) ? 'rest' : 'default';
		if (self::$cache->exists ( 'controllers/routes.' . $key ))
			return self::$cache->fetch ( 'controllers/routes.' . $key );
		return [ ];
	}

	public static function getRouteCache($routePath, $routeArray, $duration) {
		$key = self::getRouteKey ( $routePath );

		if (self::$cache->exists ( 'controllers/' . $key ) && ! self::expired ( $key, $duration )) {
			$response = self::$cache->file_get_contents ( 'controllers/' . $key );
			return $response;
		} else {
			$response = Startup::runAsString ( $routeArray );
			return self::storeRouteResponse ( $key, $response );
		}
	}

	protected static function expired($key, $duration) {
		return self::$cache->expired ( "controllers/" . $key, $duration ) === true;
	}

	public static function isExpired($routePath, $duration) {
		return self::expired ( self::getRouteKey ( $routePath ), $duration );
	}

	public static function setExpired($routePath) {
		$key = self::getRouteKey ( $routePath );
		if (self::$cache->exists ( 'controllers/' . $key )) {
			self::$cache->remove ( 'controllers/' . $key );
		}
	}

	public static function setRouteCache($routePath) {
		$key = self::getRouteKey ( $routePath );
		$response = Startup::runAsString ( $routePath );
		return self::storeRouteResponse ( $key, $response );
	}

	public static function addAdminRoutes() {
		self::addControllerCache ( 'Ubiquity\controllers\Admin' );
	}

	public static function getRoutes() {
		$result = self::getControllerCache ();
		return $result;
	}

	public static function getControllerRoutes($controllerClass, $isRest = false) {
		$result = [ ];
		$ctrlCache = self::getControllerCache ( $isRest );
		foreach ( $ctrlCache as $path => $routeAttributes ) {
			if (isset ( $routeAttributes ['controller'] )) {
				if ($routeAttributes ['controller'] === $controllerClass) {
					$result [$path] = $routeAttributes;
				}
			} else {
				$firstValue = current ( $routeAttributes );
				if (isset ( $firstValue ) && isset ( $firstValue ['controller'] )) {
					if ($firstValue ['controller'] === $controllerClass) {
						$result [$path] = $routeAttributes;
					}
				}
			}
		}
		return $result;
	}

	public static function addRoute($path, $controller, $action = 'index', $methods = null, $name = '', $isRest = false, $priority = 0, $callback = null) {
		$controllerCache = self::getControllerCache ( $isRest );
		Router::addRouteToRoutes ( $controllerCache, $path, $controller, $action, $methods, $name, false, null, [ ], $priority, $callback );
		self::$cache->store ( 'controllers/routes.' . ($isRest ? 'rest' : 'default'), "return " . UArray::asPhpArray ( $controllerCache, 'array' ) . ';', 'controllers' );
	}

	public static function addRoutes($pathArray, $controller, $action = 'index', $methods = null, $name = '') {
		self::addRoutes_ ( $pathArray, $controller, $action, $methods, $name, false );
	}

	public static function addRestRoutes($pathArray, $controller, $action = 'index', $methods = null, $name = '') {
		self::addRoutes_ ( $pathArray, $controller, $action, $methods, $name, true );
	}

	private static function addRoutes_($pathArray, $controller, $action = 'index', $methods = null, $name = '', $isRest = false) {
		$controllerCache = self::getControllerCache ( $isRest );
		$postfix = 'default';
		if ($isRest) {
			$postfix = 'rest';
		}
		Router::addRoutesToRoutes ( $controllerCache, $pathArray, $controller, $action, $methods, $name );
		self::$cache->store ( 'controllers/routes.' . $postfix, 'return ' . UArray::asPhpArray ( $controllerCache, 'array' ) . ';', 'controllers' );
	}

	public static function getControllersFiles(&$config, $silent = false) {
		return self::_getFiles ( $config, 'controllers', $silent );
	}

	public static function getControllers($subClass = "\\Ubiquity\\controllers\\Controller", $backslash = false, $includeSubclass = false, $includeAbstract = false) {
		$result = [ ];
		if ($includeSubclass) {
			$result [] = $subClass;
		}
		$config = Startup::getConfig ();
		$files = self::getControllersFiles ( $config, true );
		try {
			$restCtrls = CacheManager::getRestCache ();
		} catch ( \Exception $e ) {
			$restCtrls = [ ];
		}
		foreach ( $files as $file ) {
			if (\is_file ( $file )) {
				$controllerClass = ClassUtils::getClassFullNameFromFile ( $file, $backslash );
				if (\class_exists ( $controllerClass ) && isset ( $restCtrls [$controllerClass] ) === false) {
					$r = new \ReflectionClass ( $controllerClass );
					if ($r->isSubclassOf ( $subClass ) && ($includeAbstract || ! $r->isAbstract ())) {
						$result [] = $controllerClass;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Preloads controllers.
	 * To use only with async servers (Swoole, Workerman)
	 *
	 * @param ?array $controllers
	 */
	public static function warmUpControllers($controllers = null) {
		$controllers = $controllers ?? self::getControllers ();
		foreach ( $controllers as $ctrl ) {
			$controller = StartupAsync::getControllerInstance ( $ctrl );
			$binary = UIntrospection::implementsMethod ( $controller, 'isValid', Controller::class ) ? 1 : 0;
			$binary += UIntrospection::implementsMethod ( $controller, 'initialize', Controller::class ) ? 2 : 0;
			$binary += UIntrospection::implementsMethod ( $controller, 'finalize', Controller::class ) ? 4 : 0;
			$controller->_binaryCalls = $binary;
		}
	}
}
