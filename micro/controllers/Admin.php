<?php
namespace micro\controllers;

use micro\controllers\Controller;
use micro\cache\CacheManager;

/**
 * @route("/admin")
 */
class Admin extends Controller{
	/**
	 * @route("/routes")
	 */
	public function index(){
		print_r(CacheManager::getRoutes());
	}
}
