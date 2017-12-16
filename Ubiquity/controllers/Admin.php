<?php

namespace Ubiquity\controllers;

use Ubiquity\cache\CacheManager;
use controllers\ControllerBase;
use Ubiquity\utils\JArray;

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
