<?php

namespace Ubiquity\controllers;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;

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
			echo $path . "=>" . UArray::asPhpArray($infosroute);
		}
	}

	/**
	 * @route("/reset/cache")
	 */
	public function opCacheReset() {
		\opcache_reset();
	}
}

