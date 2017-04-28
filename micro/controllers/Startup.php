<?php

namespace micro\controllers;

use micro\utils\StrUtils;
use micro\views\engine\TemplateEngine;

class Startup {
	public static $urlParts;
	private static $config;
	private static $ctrlNS;

	public static function run(array &$config, $url) {
		@\set_exception_handler(array ('Startup','errorHandler' ));
		self::$config=$config;
		self::startTemplateEngine($config);

		session_start();

		$u=self::parseUrl($url);

		if (($ru=Router::getRoute($url)) !== false) {
			if (\is_array($ru))
				self::runAction($ru);
			else
				echo $ru;
		} else {
			self::setCtrlNS($config);
			$u[0]=self::$ctrlNS . $u[0];
			if (\class_exists($u[0])) {
				self::runAction($u);
			} else {
				print "Le contrôleur `" . $u[0] . "` n'existe pas <br/>";
			}
		}
	}

	private static function setCtrlNS($config) {
		$ns=$config["mvcNS"]["controllers"];
		if ($ns !== "" && $ns !== null) {
			$ns.="\\";
		}
		self::$ctrlNS=$ns;
	}

	private static function parseUrl(&$url) {
		if (!$url) {
			$url="_default";
		}
		if (StrUtils::endswith($url, "/"))
			$url=\substr($url, 0, strlen($url) - 1);
		self::$urlParts=\explode("/", $url);

		return self::$urlParts;
	}

	private static function startTemplateEngine($config) {
		try {
			$engineOptions=array ('cache' => ROOT . DS . "views/cache/" );
			if (isset($config["templateEngine"])) {
				$templateEngine=$config["templateEngine"];
				if (isset($config["templateEngineOptions"])) {
					$engineOptions=$config["templateEngineOptions"];
				}
				$engine=new $templateEngine($engineOptions);
				if ($engine instanceof TemplateEngine) {
					self::$config["templateEngine"]=$engine;
				}
			}
		} catch ( \Exception $e ) {
			echo $e->getTraceAsString();
		}
	}

	public static function runAction($u, $initialize=true, $finalize=true) {
		$config=self::getConfig();
		$ctrl=$u[0];
		$controller=new $ctrl();
		if (!$controller instanceof Controller) {
			print "`{$u[0]}` n'est pas une instance de contrôleur.`<br/>";
			return;
		}
		// Dependency injection
		if (\array_key_exists("di", $config)) {
			$di=$config["di"];
			if (\is_array($di)) {
				foreach ( $di as $k => $v ) {
					$controller->$k=$v();
				}
			}
		}

		if ($initialize)
			$controller->initialize();
		self::callController($controller, $u);
		if ($finalize)
			$controller->finalize();
	}

	public static function runAsString($u, $initialize=true, $finalize=true) {
		\ob_start();
		self::runAction($u, $initialize, $finalize);
		return \ob_get_clean();
	}

	private static function callController(Controller $controller, $u) {
		$urlSize=sizeof($u);
		try {
			switch($urlSize) {
				case 1:
					$controller->index();
					break;
				case 2:
					$action=$u[1];
					// Appel de la méthode (2ème élément du tableau)
					if (\method_exists($controller, $action)) {
						$controller->$action();
					} else {
						print "La méthode `{$action}` n'existe pas sur le contrôleur `" . $u[0] . "`<br/>";
					}
					break;
				default:
					// Appel de la méthode en lui passant en paramètre le reste du tableau
					\call_user_func_array(array ($controller,$u[1] ), array_slice($u, 2));
					break;
			}
		} catch ( \Exception $e ) {
			print "Error!: " . $e->getMessage() . "<br/>";
		}
	}

	public static function getConfig() {
		return self::$config;
	}

	public static function errorHandler($severity, $message, $filename, $lineno) {
		if (\error_reporting() == 0) {
			return;
		}
		if (\error_reporting() & $severity) {
			throw new \ErrorException($message, 0, $severity, $filename, $lineno);
		}
	}
}
