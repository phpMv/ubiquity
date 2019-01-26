<?php

namespace Ubiquity\controllers;

use Ubiquity\utils\base\UString;
use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\utils\http\USession;
use Ubiquity\log\Logger;
use Ubiquity\controllers\traits\StartupConfigTrait;

/**
 * Starts the framework
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class Startup {
	use StartupConfigTrait;
	public static $urlParts;
	public static $templateEngine;
	private static $controller;
	private static $action;
	private static $actionParams;

	public static function run(array &$config) {
		self::$config = $config;
		self::startTemplateEngine ( $config );
		if (isset ( $config ['sessionName'] ))
			USession::start ( $config ['sessionName'] );
		self::forward ( $_GET ['c'] );
	}

	public static function forward($url, $initialize = true, $finalize = true) {
		$u = self::parseUrl ( $url );
		if (($ru = Router::getRoute ( $url )) !== false) {
			if (\is_array ( $ru )) {
				if (is_callable ( $ru [0] )) {
					self::runCallable ( $ru );
				}else{
					self::_preRunAction( $ru, $initialize, $finalize );
				}
			} else {
				echo $ru; // Displays route response from cache
			}
		} else {
			self::setCtrlNS ();
			$u [0] = self::$ctrlNS . $u [0];
			self::_preRunAction($u,$initialize,$finalize);
		}
	}
	
	private static function _preRunAction(&$u,$initialize=true,$finalize=true){
		if (\class_exists ( $u [0] )) {
			self::runAction ( $u, $initialize, $finalize );
		}else {
			\header ( 'HTTP/1.0 404 Not Found', true, 404 );
			Logger::warn ( "Startup", "The controller `" . $u [0] . "` doesn't exists! <br/>", "forward" );
		}
	}

	private static function parseUrl(&$url) {
		if (! $url) {
			$url = "_default";
		}
		if (UString::endswith ( $url, "/" ))
			$url = \substr ( $url, 0, strlen ( $url ) - 1 );
		self::$urlParts = \explode ( "/", $url );

		return self::$urlParts;
	}

	private static function startTemplateEngine(&$config) {
		try {
			if (isset ( $config ['templateEngine'] )) {
				$templateEngine = $config ['templateEngine'];
				$engineOptions = $config ['templateEngineOptions'] ?? array ('cache' => \ROOT . \DS . 'views/cache/' );
				$engine = new $templateEngine ( $engineOptions );
				if ($engine instanceof TemplateEngine) {
					self::$templateEngine = $engine;
				}
			}
		} catch ( \Exception $e ) {
			echo $e->getTraceAsString ();
		}
	}

	public static function runAction(array &$u, $initialize = true, $finalize = true) {
		$ctrl = $u [0];
		self::$controller = $ctrl;
		self::$action = "index";
		self::$actionParams = [ ];
		if (\sizeof ( $u ) > 1)
			self::$action = $u [1];
		if (\sizeof ( $u ) > 2)
			self::$actionParams = array_slice ( $u, 2 );

		$controller = new $ctrl ();
		if (! $controller instanceof Controller) {
			print "`{$u[0]}` isn't a controller instance.`<br/>";
			return;
		}
		// Dependency injection
		self::injectDependences ( $controller );
		if (! $controller->isValid ( self::$action )) {
			$controller->onInvalidControl ();
		} else {
			if ($initialize)
				$controller->initialize ();
			self::callController ( $controller, $u );
			if ($finalize)
				$controller->finalize ();
		}
	}

	public static function runCallable(array &$u) {
		self::$actionParams = [ ];
		if (\sizeof ( $u ) > 1) {
			self::$actionParams = array_slice ( $u, 1 );
		}
		if (isset ( self::$config ['di'] )) {
			$di = self::$config ['di'];
			if (\is_array ( $di )) {
				self::$actionParams = array_merge ( self::$actionParams, $di );
			}
		}
		call_user_func_array ( $u [0], self::$actionParams );
	}

	public static function injectDependences($controller) {
		if (isset ( self::$config ['di'] )) {
			$di = self::$config ['di'];
			if (\is_array ( $di )) {
				foreach ( $di as $k => $v ) {
					$controller->$k = $v ( $controller );
				}
			}
		}
	}

	public static function runAsString(array &$u, $initialize = true, $finalize = true) {
		\ob_start ();
		self::runAction ( $u, $initialize, $finalize );
		return \ob_get_clean ();
	}

	private static function callController(Controller $controller, array &$u) {
		$urlSize = sizeof ( $u );
		switch ($urlSize) {
			case 1 :
				$controller->index ();
				break;
			case 2 :
				$action = $u [1];
				// Appel de la méthode (2ème élément du tableau)
				if (\method_exists ( $controller, $action )) {
					$controller->$action ();
				} else {
					Logger::warn ( "Startup", "The method `{$action}` doesn't exists on controller `" . $u [0] . "`", "callController" );
				}
				break;
			default :
				// Appel de la méthode en lui passant en paramètre le reste du tableau
				\call_user_func_array ( array ($controller,$u [1] ), self::$actionParams );
				break;
		}
	}

	public static function errorHandler($message = "", $code = 0, $severity = 1, $filename = null, int $lineno = 0, $previous = NULL) {
		if (\error_reporting () == 0) {
			return;
		}
		if (\error_reporting () & $severity) {
			throw new \ErrorException ( $message, 0, $severity, $filename, $lineno, $previous );
		}
	}

	public static function getController() {
		return self::$controller;
	}

	public static function getControllerSimpleName() {
		return (new \ReflectionClass ( self::$controller ))->getShortName ();
	}

	public static function getViewNameFileExtension() {
		return "html";
	}

	public static function getAction() {
		return self::$action;
	}

	public static function getActionParams() {
		return self::$actionParams;
	}

	public static function getFrameworkDir() {
		return \dirname ( __FILE__ );
	}

	public static function getApplicationDir() {
		return \dirname ( \ROOT );
	}

	public static function getApplicationName() {
		return basename ( \dirname ( \ROOT ) );
	}
}
