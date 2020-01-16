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
 * @version 1.1.6
 *
 */
class Startup {
	use StartupConfigTrait;
	public static $urlParts;
	public static $templateEngine;
	protected static $controller;
	protected static $action;
	protected static $actionParams;

	protected static function parseUrl(&$url): array {
		if (! $url) {
			$url = '_default';
		}
		return self::$urlParts = \explode ( '/', \rtrim ( $url, '/' ) );
	}

	protected static function _getControllerInstance($controllerName): ?object {
		if (\class_exists ( $controllerName, true )) {
			$controller = new $controllerName ();
			// Dependency injection
			if (isset ( self::$config ['di'] ) && \is_array ( self::$config ['di'] )) {
				self::injectDependences ( $controller );
			}
			return $controller;
		} else {
			Logger::warn ( 'Startup', 'The controller `' . $controllerName . '` doesn\'t exists! <br/>', 'runAction' );
			self::getHttpInstance ()->header ( 'HTTP/1.0 404 Not Found', '', true, 404 );
			return null;
		}
	}

	protected static function startTemplateEngine(&$config): void {
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
	public static function run(array &$config): void {
		self::init ( $config );
		self::forward ( $_GET ['c'] );
	}

	/**
	 * Initialize the app with $config array
	 *
	 * @param array $config
	 */
	public static function init(array &$config): void {
		self::$config = $config;
		if (isset ( $config ['templateEngine'] )) {
			self::startTemplateEngine ( $config );
		}
		if (isset ( $config ['sessionName'] )) {
			USession::start ( $config ['sessionName'] );
		}
		self::$ctrlNS = self::getNS ();
	}

	/**
	 * Forwards to url
	 *
	 * @param string $url The url to forward to
	 * @param boolean $initialize If true, the **initialize** method of the controller is called
	 * @param boolean $finalize If true, the **finalize** method of the controller is called
	 */
	public static function forward($url, $initialize = true, $finalize = true): void {
		$u = self::parseUrl ( $url );
		if (\is_array ( Router::getRoutes () ) && ($ru = Router::getRoute ( $url, true, self::$config ['debug'] ?? false)) !== false) {
			if (\is_array ( $ru )) {
				if (\is_string ( $ru [0] )) {
					static::runAction ( $ru, $initialize, $finalize );
				} else {
					self::runCallable ( $ru );
				}
			} else {
				echo $ru; // Displays route response from cache
			}
		} else {
			$u [0] = self::$ctrlNS . $u [0];
			static::runAction ( $u, $initialize, $finalize );
		}
	}

	/**
	 * Returns the template engine instance
	 *
	 * @return TemplateEngine
	 */
	public static function getTempateEngineInstance(): ?TemplateEngine {
		$config = self::$config;
		if (isset ( $config ['templateEngine'] )) {
			$templateEngine = $config ['templateEngine'];
			return new $templateEngine ( [ ] );
		}
		return null;
	}

	/**
	 * Runs an action on a controller
	 *
	 * @param array $u An array containing controller, action and parameters
	 * @param boolean $initialize If true, the **initialize** method of the controller is called
	 * @param boolean $finalize If true, the **finalize** method of the controller is called
	 */
	public static function runAction(array &$u, $initialize = true, $finalize = true): void {
		self::$controller = $ctrl = $u [0];
		self::$action = $action = $u [1] ?? 'index';
		self::$actionParams = \array_slice ( $u, 2 );

		try {
			if (null !== $controller = self::_getControllerInstance ( $ctrl )) {
				if (! $controller->isValid ( $action )) {
					$controller->onInvalidControl ();
				} else {
					if ($initialize) {
						$controller->initialize ();
					}
					try {
						$controller->$action ( ...(self::$actionParams) );
					} catch ( \Error $e ) {
						Logger::warn ( 'Startup', $e->getTraceAsString (), 'runAction' );
						if (self::$config ['debug']) {
							throw $e;
						}
					}
					if ($finalize) {
						$controller->finalize ();
					}
				}
			}
		} catch ( \Error $eC ) {
			Logger::warn ( 'Startup', $eC->getTraceAsString (), 'runAction' );
			if (self::$config ['debug']) {
				throw $eC;
			}
		}
	}

	/**
	 * Runs a callback
	 *
	 * @param array $u An array containing a callback, and some parameters
	 */
	public static function runCallable(array &$u): void {
		self::$actionParams = \array_slice ( $u, 1 );
		if (isset ( self::$config ['di'] )) {
			$di = self::$config ['di'];
			if (\is_array ( $di )) {
				self::$actionParams += $di;
			}
		}
		$func = $u [0];
		$func ( ...(self::$actionParams) );
	}

	/**
	 * Injects the dependencies from the **di** config key in a controller
	 *
	 * @param Controller $controller The controller
	 */
	public static function injectDependences($controller): void {
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
	public static function runAsString(array &$u, $initialize = true, $finalize = true): string {
		\ob_start ();
		self::runAction ( $u, $initialize, $finalize );
		return \ob_get_clean ();
	}

	public static function errorHandler($message = '', $code = 0, $severity = 1, $filename = null, int $lineno = 0, $previous = NULL) {
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
	public static function getController(): string {
		return self::$controller;
	}

	/**
	 * Returns the class simple name of the active controller
	 *
	 * @return string
	 */
	public static function getControllerSimpleName(): string {
		return (new \ReflectionClass ( self::$controller ))->getShortName ();
	}

	/**
	 * Returns the extension for view files
	 *
	 * @return string
	 */
	public static function getViewNameFileExtension(): string {
		return "html";
	}

	/**
	 * Returns tha active action
	 *
	 * @return string
	 */
	public static function getAction(): string {
		return self::$action;
	}

	/**
	 * Returns the active parameters
	 *
	 * @return array
	 */
	public static function getActionParams(): array {
		return self::$actionParams;
	}

	/**
	 * Returns the framework directory
	 *
	 * @return string
	 */
	public static function getFrameworkDir(): string {
		return \dirname ( __FILE__ );
	}

	/**
	 * Returns the application directory (app directory)
	 *
	 * @return string
	 */
	public static function getApplicationDir(): string {
		return \dirname ( \ROOT );
	}

	/**
	 * Returns the application name
	 *
	 * @return string
	 */
	public static function getApplicationName(): string {
		return \basename ( \dirname ( \ROOT ) );
	}
}
