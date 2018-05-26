<?php

namespace Ubiquity\cache\traits;

use Ubiquity\controllers\Startup;
use Ubiquity\controllers\Router;
use Ubiquity\cache\parser\ControllerParser;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UArray;
use Ubiquity\cache\CacheManager;

/**
 *
 * @author jc
 * @staticvar array $cache
 *
 */
trait RouterCacheTrait{

	abstract protected static function _getFiles(&$config, $type, $silent=false);
	private static $expiredRoutes=[ ];

	private static function addControllerCache($classname) {
		$parser=new ControllerParser();
		try {
			$parser->parse($classname);
			return $parser->asArray();
		} catch ( \Exception $e ) {
			// Nothing to do
		}
		return [ ];
	}

	private static function initRouterCache(&$config, $silent=false) {
		$routes=[ "rest" => [ ],"default" => [ ] ];
		$files=self::getControllersFiles($config);
		foreach ( $files as $file ) {
			if (is_file($file)) {
				$controller=ClassUtils::getClassFullNameFromFile($file);
				$parser=new ControllerParser();
				try {
					$parser->parse($controller);
					$ret=$parser->asArray();
					$key=($parser->isRest()) ? "rest" : "default";
					$routes[$key]=\array_merge($routes[$key], $ret);
				} catch ( \Exception $e ) {
					// Nothing to do
				}
			}
		}
		self::$cache->store("controllers/routes.default", "return " . UArray::asPhpArray($routes["default"], "array") . ";", 'controllers');
		self::$cache->store("controllers/routes.rest", "return " . UArray::asPhpArray($routes["rest"], "array") . ";", 'controllers');
		if (!$silent) {
			echo "Router cache reset\n";
		}
	}

	private static function storeRouteResponse($key, $response) {
		self::setKeyExpired($key, false);
		self::$cache->store("controllers/" . $key, $response, 'controllers', false);
		return $response;
	}

	private static function getRouteKey($routePath) {
		return "path" . \md5(\implode("", $routePath));
	}

	private static function setKeyExpired($key, $expired=true) {
		if ($expired) {
			self::$expiredRoutes[$key]=true;
		} else {
			unset(self::$expiredRoutes[$key]);
		}
	}

	public static function getControllerCache($isRest=false) {
		$key=($isRest) ? "rest" : "default";
		if (self::$cache->exists("controllers/routes." . $key))
			return self::$cache->fetch("controllers/routes." . $key);
		return [ ];
	}

	public static function getRouteCache($routePath, $duration) {
		$key=self::getRouteKey($routePath);

		if (self::$cache->exists("controllers/" . $key) && !self::expired($key, $duration)) {
			$response=self::$cache->file_get_contents("controllers/" . $key);
			return $response;
		} else {
			$response=Startup::runAsString($routePath);
			return self::storeRouteResponse($key, $response);
		}
	}

	public static function expired($key, $duration) {
		return self::$cache->expired("controllers/" . $key, $duration) === true || \array_key_exists($key, self::$expiredRoutes);
	}

	public static function isExpired($path, $duration) {
		$route=Router::getRoute($path, false);
		if ($route !== false && \is_array($route)) {
			return self::expired(self::getRouteKey($route), $duration);
		}
		return true;
	}

	public static function setExpired($routePath, $expired=true) {
		$key=self::getRouteKey($routePath);
		self::setKeyExpired($key, $expired);
	}

	public static function setRouteCache($routePath) {
		$key=self::getRouteKey($routePath);
		$response=Startup::runAsString($routePath);
		return self::storeRouteResponse($key, $response);
	}

	public static function addAdminRoutes() {
		self::addControllerCache("Ubiquity\controllers\Admin");
	}

	public static function getRoutes() {
		$result=self::getControllerCache();
		return $result;
	}

	public static function getControllerRoutes($controllerClass, $isRest=false) {
		$result=[ ];
		$ctrlCache=self::getControllerCache($isRest);
		foreach ( $ctrlCache as $path => $routeAttributes ) {
			if (isset($routeAttributes["controller"])) {
				if ($routeAttributes["controller"] === $controllerClass) {
					$result[$path]=$routeAttributes;
				}
			} else {
				$firstValue=reset($routeAttributes);
				if (isset($firstValue) && isset($firstValue["controller"])) {
					if ($firstValue["controller"] === $controllerClass) {
						$result[$path]=$routeAttributes;
					}
				}
			}
		}
		return $result;
	}

	public static function addRoute($path, $controller, $action="index", $methods=null, $name="") {
		$controllerCache=self::getControllerCache();
		Router::addRouteToRoutes($controllerCache, $path, $controller, $action, $methods, $name);
		self::$cache->store("controllers/routes", "return " . UArray::asPhpArray($controllerCache, "array") . ";", 'controllers');
	}

	public static function getControllersFiles(&$config, $silent=false) {
		return self::_getFiles($config, "controllers", $silent);
	}

	public static function getControllers($subClass="\\Ubiquity\\controllers\\Controller",$backslash=false,$includeSubclass=false) {
		$result=[ ];
		if($includeSubclass){
			$result[]=$subClass;
		}
		$config=Startup::getConfig();
		$files=self::getControllersFiles($config, true);
		try {
			$restCtrls=CacheManager::getRestCache();
		} catch ( \Exception $e ) {
			$restCtrls=[ ];
		}
		foreach ( $files as $file ) {
			if (is_file($file)) {
				$controllerClass=ClassUtils::getClassFullNameFromFile($file,$backslash);
				if (isset($restCtrls[$controllerClass]) === false) {
					$r=new \ReflectionClass($controllerClass);
					if($r->isSubclassOf($subClass) && !$r->isAbstract()){
						$result[]=$controllerClass;
					}
				}
			}
		}
		return $result;
	}
}
