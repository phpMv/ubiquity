<?php

/**
 * This class gives access to useful methods or objects of the framework
 * @author jc
 * @version 1.0.0.1
 *
 */
namespace Ubiquity\core;

use Ubiquity\controllers\Startup;
use Ubiquity\controllers\Router;
use Ubiquity\utils\RequestUtils;
use Ubiquity\utils\SessionUtils;
use Ubiquity\utils\CookieUtils;

class Framework {

	public const version='2.0.0-beta.1';

	public static function getController(){
		return Startup::getController();
	}
	public static function getAction(){
		return Startup::getAction();
	}

	public static function getUrl(){
		return \implode("/", Startup::$urlParts);
	}

	public static function getRouter(){
		return new Router();
	}

	public static function getRequest(){
		return new RequestUtils();
	}

	public static function getSession(){
		return new SessionUtils();
	}

	public static function getCookies(){
		return new CookieUtils();
	}

	public static function hasAdmin(){
		return \class_exists("controllers\Admin");
	}
}

