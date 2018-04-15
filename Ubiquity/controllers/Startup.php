<?php

namespace Ubiquity\controllers;

use Ubiquity\utils\base\UString;
use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\base\UFileSystem;

class Startup {
	public static $urlParts;
	private static $config;
	private static $ctrlNS;
	private static $controller;
	private static $action;

	public static function run(array &$config, $url) {
		self::$config = $config;
		self::startTemplateEngine ( $config );
		if (isset ( $config ["sessionName"] ))
			USession::start ( $config ["sessionName"] );
		self::forward ( $url );
	}

	public static function forward($url) {
		$u = self::parseUrl ( $url );
		if (($ru = Router::getRoute ( $url )) !== false) {
			if (\is_array ( $ru ))
				self::runAction ( $ru );
			else
				echo $ru;
		} else {
			self::setCtrlNS ();
			$u [0] = self::$ctrlNS . $u [0];
			if (\class_exists ( $u [0] )) {
				self::runAction ( $u );
			} else {
				\header ( 'HTTP/1.0 404 Not Found', true, 404 );
				print "Le contrôleur `" . $u [0] . "` n'existe pas <br/>";
			}
		}
	}

	public static function getNS($part = "controllers") {
		$config = self::$config;
		$ns = $config ["mvcNS"] [$part];
		if ($ns !== "" && $ns !== null) {
			$ns .= "\\";
		}
		return $ns;
	}

	private static function setCtrlNS() {
		self::$ctrlNS = self::getNS ();
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

	private static function startTemplateEngine($config) {
		try {
			$engineOptions = array ('cache' => ROOT . DS . "views/cache/" );
			if (isset ( $config ["templateEngine"] )) {
				$templateEngine = $config ["templateEngine"];
				if (isset ( $config ["templateEngineOptions"] )) {
					$engineOptions = $config ["templateEngineOptions"];
				}
				$engine = new $templateEngine ( $engineOptions );
				if ($engine instanceof TemplateEngine) {
					self::$config ["templateEngine"] = $engine;
				}
			}
		} catch ( \Exception $e ) {
			echo $e->getTraceAsString ();
		}
	}

	public static function runAction($u, $initialize = true, $finalize = true) {
		$config = self::getConfig ();
		$ctrl = $u [0];
		self::$controller = $ctrl;
		self::$action = "index";
		if (\sizeof ( $u ) > 1)
			self::$action = $u [1];

		$controller = new $ctrl ();
		if (! $controller instanceof Controller) {
			print "`{$u[0]}` n'est pas une instance de contrôleur.`<br/>";
			return;
		}
		// Dependency injection
		if (\array_key_exists ( "di", $config )) {
			$di = $config ["di"];
			if (\is_array ( $di )) {
				foreach ( $di as $k => $v ) {
					$controller->$k = $v ( $controller );
				}
			}
		}

		if ($initialize)
			$controller->initialize ();
		self::callController ( $controller, $u );
		if ($finalize)
			$controller->finalize ();
	}

	public static function runAsString($u, $initialize = true, $finalize = true) {
		\ob_start ();
		self::runAction ( $u, $initialize, $finalize );
		return \ob_get_clean ();
	}

	private static function callController(Controller $controller, $u) {
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
					print "La méthode `{$action}` n'existe pas sur le contrôleur `" . $u [0] . "`<br/>";
				}
				break;
			default :
				// Appel de la méthode en lui passant en paramètre le reste du tableau
				\call_user_func_array ( array ($controller,$u [1] ), array_slice ( $u, 2 ) );
				break;
		}
	}

	public static function getConfig() {
		return self::$config;
	}

	public static function setConfig($config) {
		self::$config = $config;
	}

	private static function needsKeyInConfigArray(&$result, $array, $needs) {
		foreach ( $needs as $need ) {
			if (! isset ( $array [$need] ) || UString::isNull ( $array [$need] )) {
				$result [] = $need;
			}
		}
	}

	public static function checkDbConfig() {
		$config = self::$config;
		$result = [ ];
		$needs = [ "type","dbName","serverName" ];
		if (! isset ( $config ["database"] )) {
			$result [] = "database";
		} else {
			self::needsKeyInConfigArray ( $result, $config ["database"], $needs );
		}
		return $result;
	}

	public static function checkModelsConfig() {
		$config = self::$config;
		$result = [ ];
		if (! isset ( $config ["mvcNS"] )) {
			$result [] = "mvcNS";
		} else {
			self::needsKeyInConfigArray ( $result, $config ["mvcNS"], [ "models" ] );
		}
		return $result;
	}

	public static function getModelsDir() {
		return self::$config ["mvcNS"] ["models"];
	}

	public static function getModelsCompletePath() {
		return ROOT . DS . self::getModelsDir ();
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

	public static function getAction() {
		return self::$action;
	}

	public static function getFrameworkDir() {
		return \dirname ( __FILE__ );
	}

	public static function getApplicationDir() {
		return \dirname ( ROOT );
	}
	
	public static function getApplicationName() {
		return basename(\dirname ( ROOT ));
	}
	
	public static function reloadConfig(){
		$appDir=\dirname ( ROOT );
		$filename=$appDir."/app/config/config.php";
		self::$config=include($filename);
		self::startTemplateEngine(self::$config);
		return self::$config;
	}
	
	public static function saveConfig($content){
		$appDir=\dirname ( ROOT );
		$filename=$appDir."/app/config/config.php";
		$oldFilename=$appDir."/app/config/config.old.php";
		if (!file_exists($filename) || copy($filename, $oldFilename)) {
			return UFileSystem::save($filename,$content);
		}
		return false;
	}
}
