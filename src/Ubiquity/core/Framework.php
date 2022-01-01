<?php

/**
 * Ubiquity\core
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.1
 *
 */
namespace Ubiquity\core;

use Ubiquity\assets\AssetsManager;
use Ubiquity\contents\normalizers\NormalizersManager;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\orm\OrmUtils;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\http\UCookie;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;
use Ubiquity\cache\CacheManager;

class Framework {
	public const version = '2.4.9';

	public static function getVersion() {
		return self::version;
	}

	public static function getController() {
		return Startup::getController ();
	}

	public static function getAction() {
		return Startup::getAction ();
	}

	public static function getUrl() {
		return \implode ( '/', Startup::$urlParts );
	}

	public static function getRouter() {
		return new Router ();
	}

	public static function getORM() {
		return new OrmUtils ();
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

	public static function getTranslator() {
		return new TranslatorManager ();
	}

	public static function getNormalizer() {
		return new NormalizersManager ();
	}

	public static function hasAdmin() {
		return \class_exists ( "controllers\Admin" );
	}

	public static function getAssets() {
		return new AssetsManager ();
	}

	public static function getCacheSystem() {
		return \get_class ( CacheManager::$cache );
	}

	public static function getAnnotationsEngine() {
		return \get_class ( CacheManager::getAnnotationsEngineInstance () );
	}

	/**
	 * Returns an instance of JsUtils initialized with Semantic (for di injection)
	 *
	 * @param \Ubiquity\controllers\Controller $controller
	 * @param array $options
	 * @return \Ajax\php\ubiquity\JsUtils
	 * @deprecated use Ajax\php\ubiquity\JsUtils::diSemantic(...) instead.
	 */
	public static function diSemantic($controller, $options = [ 'defer' => true,'gc' => true ]) {
		$jquery = new \Ajax\php\ubiquity\JsUtils ( $options, $controller );
		$jquery->semantic ( new \Ajax\Semantic () );
		$jquery->setAjaxLoader ( "<div class=\"ui active centered inline text loader\">Loading</div>" );
		return $jquery;
	}

	/**
	 * Returns an instance of JsUtils initialized with Bootstrap (for di injection)
	 *
	 * @param \Ubiquity\controllers\Controller $controller
	 * @param array $options
	 * @return \Ajax\php\ubiquity\JsUtils
	 * @deprecated use Ajax\php\ubiquity\JsUtils::diBootstrap(...) instead.
	 */
	public static function diBootstrap($controller, $options = [ 'defer' => true,'gc' => true ]) {
		$jquery = new \Ajax\php\ubiquity\JsUtils ( $options, $controller );
		$jquery->bootstrap ( new \Ajax\Bootstrap () );
		$jquery->setAjaxLoader ( "<div class=\"ui active centered inline text loader\">Loading</div>" );
		return $jquery;
	}
}

