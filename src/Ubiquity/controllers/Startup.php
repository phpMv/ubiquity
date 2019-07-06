<?php

namespace Ubiquity\controllers;

use Ubiquity\controllers\di\DiManager;
use Ubiquity\controllers\traits\StartupConfigTrait;
use Ubiquity\log\Logger;
use Ubiquity\utils\http\USession;
use Ubiquity\views\engine\TemplateEngine;

/**
 * Starts the framework.
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.4
 *
 */
class Startup {
	use StartupConfigTrait;
	public static $urlParts;
	public static $templateEngine;
	private static $controller;
	private static $action;
	private static $actionParams;

	private static function parseUrl(&$url) {
		if (! $url) {
			$url = "_default";
		}
		return self::$urlParts = \explode ( "/", \rtrim ( $url, '/' ) );
	}

	private static function startTemplateEngine(&$config) {
		try {
			$templateEngine = $config ['templateEngine'];
			$engineOptions = $config ['templateEngineOptions'] ?? array ('cache' => false );
			$engine = new $templateEngine ( $engineOptions );
			if ($engine instanceof TemplateEngine) {
				self::$templateEngine = $engine;
			}
		} catch ( \Exception $e ) {
			echo $e->getTraceAsString ();
		}
	}

	/**
	 * Handles the request
	 *
	 * @param array $config The loaded config array
	 */
	public static function run(array &$config) {
		self::$config = $config;
		if (isset ( $config ['templateEngine'] )) {
			self::startTemplateEngine ( $config );
		}
		if (isset ( $config ['sessionName'] )) {
			USession::start ( $config ['sessionName'] );
		}
		self::forward ( $_GET ['c'] );
	}

	/**
	 * Forwards to url
	 *
	 * @param string $url The url to forward to
	 * @param boolean $initialize If true, the **initialize** method of the controller is called
	 * @param boolean $finalize If true, the **finalize** method of the controller is called
	 */
	public static function forward($url, $initialize = true, $finalize = true) {
		$u = self::parseUrl ( $url );
		if (\is_array ( Router::getRoutes () ) && ($ru = Router::getRoute ( $url, true, self::$config ['debug'] ?? false)) !== false) {
			if (\is_array ( $ru )) {
				if (\is_string ( $ru [0] )) {
					self::runAction ( $ru, $initialize, $finalize );
				} else {
					self::runCallable ( $ru );
				}
			} else {
				echo $ru; // Displays route response from cache
			}
		} else {
			$u [0] = self::setCtrlNS () . $u [0];
			self::runAction ( $u, $initialize, $finalize );
		}
	}

	/**
	 * Returns the template engine instance
	 *
	 * @return TemplateEngine
	 */
	public static function getTempateEngineInstance() {
		$config = self::$config;
		if (isset ( $config ['templateEngine'] )) {
			$templateEngine = $config ['templateEngine'];
			return new $templateEngine ( [ ] );
		}
	}

	/**
	 * Runs an action on a controller
	 *
	 * @param array $u An array containing controller, action and parameters
	 * @param boolean $initialize If true, the **initialize** method of the controller is called
	 * @param boolean $finalize If true, the **finalize** method of the controller is called
	 */
	public static function runAction(array &$u, $initialize = true, $finalize = true) {
		$ctrl = $u [0];
		$uSize = \sizeof ( $u );
		self::$controller = $ctrl;
		self::$action = ($uSize > 1) ? $u [1] : 'index';
		self::$actionParams = ($uSize > 2) ? \array_slice ( $u, 2 ) : [ ];

		try {
			$controller = new $ctrl ();
			// Dependency injection
			if (isset ( self::$config ['di'] ) && \is_array ( self::$config ['di'] )) {
				self::injectDependences ( $controller );
			}
			if (! $controller->isValid ( self::$action )) {
				$controller->onInvalidControl ();
			} else {
				if ($initialize) {
					$controller->initialize ();
				}
				try {
					\call_user_func_array ( [ $controller,self::$action ], self::$actionParams );
				} catch ( \Error $e ) {
					Logger::warn ( "Startup", \sprintf ( "The method `%s` doesn't exists on controller `%s`", self::$action, $ctrl ), "runAction" );
				}
				if ($finalize) {
					$controller->finalize ();
				}
			}
		} catch ( \Error $e ) {
			self::getHttpInstance ()->header ( 'HTTP/1.0 404 Not Found', '', true, 404 );
			Logger::warn ( 'Startup', 'The controller `' . $ctrl . '` doesn\'t exists! <br/>', 'runAction' );
		}
	}

	/**
	 * Runs a callback
	 *
	 * @param array $u An array containing a callback, and some parameters
	 */
	public static function runCallable(array &$u) {
		self::$actionParams = [ ];
		if (\sizeof ( $u ) > 1) {
			self::$actionParams = \array_slice ( $u, 1 );
		}
		if (isset ( self::$config ['di'] )) {
			$di = self::$config ['di'];
			if (\is_array ( $di )) {
				self::$actionParams = \array_merge ( self::$actionParams, $di );
			}
		}
		\call_user_func_array ( $u [0], self::$actionParams );
	}

	/**
	 * Injects the dependencies from the **di** config key in a controller
	 *
	 * @param Controller $controller The controller
	 */
	public static function injectDependences($controller) {
		$di = DiManager::fetch ( $controller );
		if ($di !== false) {
			foreach ( $di as $k => $v ) {
				$setter = 'set' . ucfirst ( $k );
				if (\method_exists ( $controller, $setter )) {
					$controller->$setter ( $v ( $controller ) );
				} else {
					$controller->$k = $v ( $controller );
				}
			}
		}

		$di = self::$config ['di'] ?? [ ];
		if (isset ( $di ['@exec'] )) {
			foreach ( $di ['@exec'] as $k => $v ) {
				$controller->$k = $v ( $controller );
			}
		}
	}

	/**
	 * Runs an action on a controller and returns a string
	 *
	 * @param array $u
	 * @param boolean $initialize If true, the **initialize** method of the controller is called
	 * @param boolean $finalize If true, the **finalize** method of the controller is called
	 * @return string
	 */
	public static function runAsString(array &$u, $initialize = true, $finalize = true) {
		\ob_start ();
		self::runAction ( $u, $initialize, $finalize );
		return \ob_get_clean ();
	}

	public static function errorHandler($message = "", $code = 0, $severity = 1, $filename = null, int $lineno = 0, $previous = NULL) {
		if (\error_reporting () == 0) {
			return;
		}
		if (\error_reporting () & $severity) {
			throw new \ErrorException ( $message, 0, $severity, $filename, $lineno, $previous );
		}
	}

	/**
	 * Returns the active controller name
	 *
	 * @return string
	 */
	public static function getController() {
		return self::$controller;
	}

	/**
	 * Returns the class simple name of the active controller
	 *
	 * @return string
	 */
	public static function getControllerSimpleName() {
		return (new \ReflectionClass ( self::$controller ))->getShortName ();
	}

	/**
	 * Returns the extension for view files
	 *
	 * @return string
	 */
	public static function getViewNameFileExtension() {
		return "html";
	}

	/**
	 * Returns tha active action
	 *
	 * @return string
	 */
	public static function getAction() {
		return self::$action;
	}

	/**
	 * Returns the active parameters
	 *
	 * @return array
	 */
	public static function getActionParams() {
		return self::$actionParams;
	}

	/**
	 * Returns the framework directory
	 *
	 * @return string
	 */
	public static function getFrameworkDir() {
		return \dirname ( __FILE__ );
	}

	/**
	 * Returns the application directory (app directory)
	 *
	 * @return string
	 */
	public static function getApplicationDir() {
		return \dirname ( \ROOT );
	}

	/**
	 * Returns the application name
	 *
	 * @return string
	 */
	public static function getApplicationName() {
		return \basename ( \dirname ( \ROOT ) );
	}
}
