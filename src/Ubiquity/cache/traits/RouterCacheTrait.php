<?php

namespace Ubiquity\cache\traits;

use Ubiquity\controllers\Controller;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\StartupAsync;
use Ubiquity\domains\DDDManager;
use Ubiquity\utils\base\UIntrospection;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\UResponse;

/**
 *
 * Ubiquity\cache\traits$RouterCacheTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.14
 * @property \Ubiquity\cache\system\AbstractDataCache $cache
 *
 */
trait RouterCacheTrait {

	abstract public static function getControllers(string $subClass = "\\Ubiquity\\controllers\\Controller", bool $backslash = false, bool $includeSubclass = false, bool $includeAbstract = false): array;

	public static function controllerCacheUpdated(array &$config): array {
		$result = [];
		$domain = DDDManager::getActiveDomain();
		$newRoutes = self::parseControllerFiles($config, true, $domain != '');
		$ctrls = self::getControllerCacheByDomain(false, $domain);
		if ($newRoutes ['default'] != $ctrls && !(false)) {
			$result ['default'] = true;
		}
		$ctrls = self::getControllerCacheByDomain(true, $domain);
		if ($newRoutes ['rest'] != $ctrls) {
			$result ['rest'] = true;
		}
		return $result;
	}

	public static function storeDynamicRoutes(bool $isRest = false): void {
		$routes = Router::getRoutes();
		$part = ($isRest) ? 'rest' : 'default';
		self::$cache->store('controllers/routes.' . $part, $routes, 'controllers');
	}

	private static function storeRouteResponse(string $key, ?string $response): ?string {
		$cache = ['content-type' => UResponse::$headers ['Content-Type'] ?? 'text/html', 'content' => $response];
		self::$cache->store('controllers/' . $key, $cache, 'controllers');
		return $response;
	}

	private static function getRouteKey(string $routePath): string {
		return 'path' . \md5(Router::slashPath($routePath));
	}

	/**
	 *
	 * @param boolean $isRest
	 * @return array
	 */
	public static function getControllerCache(bool $isRest = false): array {
		$key = ($isRest) ? 'rest' : 'default';
		if (self::$cache->exists('controllers/routes.' . $key)) {
			return self::$cache->fetch('controllers/routes.' . $key);
		}
		return [];
	}

	/**
	 *
	 * @param boolean $isRest
	 * @param string $domain
	 * @return array
	 */
	public static function getControllerCacheByDomain(bool $isRest = false, string $domain = ''): array {
		$key = ($isRest) ? 'rest' : 'default';
		if (self::$cache->exists('controllers/routes.' . $key)) {
			if ($domain == '') {
				return self::$cache->fetch('controllers/routes.' . $key);
			} else {
				$ns = Startup::getNS();
				$routes = self::$cache->fetch('controllers/routes.' . $key);
				$result = [];
				foreach ($routes as $k => $route) {
					if (isset($route['controller'])) {
						if (UString::startswith($route['controller'], $ns)) {
							$result[$k] = $route;
						}
					} else {
						foreach ($route as $method => $routePart) {
							if (UString::startswith($routePart['controller'], $ns)) {
								$result[$k][$method] = $routePart;
							}
						}
					}
				}
				return $result;
			}
		}
		return [];
	}

	/**
	 *
	 * @param boolean $isRest
	 * @return array
	 */
	public static function getControllerCacheIndex(bool $isRest = false): array {
		$key = ($isRest) ? 'rest-index' : 'default-index';
		if (self::$cache->exists('controllers/routes.' . $key)) {
			return self::$cache->fetch('controllers/routes.' . $key);
		}
		return [];
	}

	public static function getRouteCache(string $routePath, array $routeArray, int $duration) {
		$key = self::getRouteKey($routePath);

		if (self::$cache->exists('controllers/' . $key) && !self::expired($key, $duration)) {
			$response = self::$cache->fetch('controllers/' . $key);
			if ($ct = $response ['content-type'] ?? false) {
				UResponse::setContentType($ct);
			}
			return $response ['content'] ?? '';
		} else {
			$response = Startup::runAsString($routeArray);
			return self::storeRouteResponse($key, $response);
		}
	}

	protected static function expired(string $key, int $duration): bool {
		return self::$cache->expired("controllers/" . $key, $duration) === true;
	}

	public static function isExpired(string $routePath, int $duration): bool {
		return self::expired(self::getRouteKey($routePath), $duration);
	}

	public static function setExpired(string $routePath): void {
		$key = self::getRouteKey($routePath);
		if (self::$cache->exists('controllers/' . $key)) {
			self::$cache->remove('controllers/' . $key);
		}
	}

	/**
	 * Stores a route response in cache.
	 * @param string $routePath The route path
	 * @return string|null
	 */
	public static function setRouteCache(string $routePath): ?string {
		$key = self::getRouteKey($routePath);
		\ob_start();
		Startup::forward($routePath);
		$response = \ob_get_clean();
		return self::storeRouteResponse($key, $response);
	}

	public static function addAdminRoutes(): void {
		self::addControllerCache('Ubiquity\controllers\Admin');
	}

	public static function getRoutes(): array {
		return self::getControllerCache();
	}

	public static function getControllerRoutes(string $controllerClass, bool $isRest = false): array {
		$result = [];
		$ctrlCache = self::getControllerCache($isRest);
		foreach ($ctrlCache as $path => $routeAttributes) {
			if (isset ($routeAttributes ['controller'])) {
				if ($routeAttributes ['controller'] === $controllerClass) {
					$result [$path] = $routeAttributes;
				}
			} else {
				$firstValue = current($routeAttributes);
				if (isset ($firstValue) && isset ($firstValue ['controller'])) {
					if ($firstValue ['controller'] === $controllerClass) {
						$result [$path] = $routeAttributes;
					}
				}
			}
		}
		return $result;
	}

	public static function addRoute(string $path, string $controller, string $action = 'index', ?array $methods = null, string $name = '', bool $isRest = false, int $priority = 0, $callback = null) {
		$controllerCache = self::getControllerCache($isRest);
		Router::addRouteToRoutes($controllerCache, $path, $controller, $action, $methods, $name, false, null, [], $priority, $callback);
		self::$cache->store('controllers/routes.' . ($isRest ? 'rest' : 'default'), $controllerCache, 'controllers');
	}

	public static function addRoutes($pathArray, $controller, $action = 'index', $methods = null, $name = '') {
		self::addRoutes_($pathArray, $controller, $action, $methods, $name, false);
	}

	public static function addRestRoutes($pathArray, $controller, $action = 'index', $methods = null, $name = '') {
		self::addRoutes_($pathArray, $controller, $action, $methods, $name, true);
	}

	private static function addRoutes_($pathArray, $controller, $action = 'index', $methods = null, $name = '', $isRest = false) {
		$controllerCache = self::getControllerCache($isRest);
		$postfix = 'default';
		if ($isRest) {
			$postfix = 'rest';
		}
		Router::addRoutesToRoutes($controllerCache, $pathArray, $controller, $action, $methods, $name);
		self::$cache->store('controllers/routes.' . $postfix, $controllerCache, 'controllers');
	}

	/**
	 * Preloads controllers.
	 * To use only with async servers (Swoole, Workerman)
	 *
	 * @param ?array $controllers
	 */
	public static function warmUpControllers(?array $controllers = null) {
		$controllers ??= self::getControllers();
		foreach ($controllers as $ctrl) {
			$controller = StartupAsync::getControllerInstance($ctrl);
			$binary = UIntrospection::implementsMethod($controller, 'isValid', Controller::class) ? 1 : 0;
			$binary += UIntrospection::implementsMethod($controller, 'initialize', Controller::class) ? 2 : 0;
			$binary += UIntrospection::implementsMethod($controller, 'finalize', Controller::class) ? 4 : 0;
			$controller->_binaryCalls = $binary;
		}
	}
}
