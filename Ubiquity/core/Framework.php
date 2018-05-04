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
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\UCookie;
use Ubiquity\orm\OrmUtils;

class Framework {
	public const version = '2.0.3';

	public static function getController() {
		return Startup::getController ();
	}

	public static function getAction() {
		return Startup::getAction ();
	}

	public static function getUrl() {
		return \implode ( "/", Startup::$urlParts );
	}

	public static function getRouter() {
		return new Router ();
	}
	
	public static function getORM() {
		return new OrmUtils();
	}

	public static function getRequest() {
		return new URequest ();
	}

	public static function getSession() {
		return new USession ();
	}

	public static function getCookies() {
		return new UCookie ();
	}

	public static function hasAdmin() {
		return \class_exists ( "controllers\Admin" );
	}
}

