<?php

namespace micro\controllers;

use micro\cache\CacheManager;
use controllers\ControllerBase;
use micro\utils\JArray;

/**
 * @route("/admin")
 */
class Admin extends ControllerBase {

	/**
	 * @route("/routes")
	 */
	public function index() {
		$routes=CacheManager::getRoutes();
		foreach ( $routes as $path => $infosroute ) {
			echo $path . "=>" . JArray::asPhpArray($infosroute);
		}
	}

	/**
	 * @route("/reset/cache")
	 */
	public function opCacheReset() {
		\opcache_reset();
	}
}
