<?php
namespace Ubiquity\controllers;

/**
 * Classe Autoloader
 * @author jc
 * @version 1.0.0.3
 * @package controllers
 */
class Autoloader {
	private static $config;
	private static $namespaces;

	public static function register($config) {
		self::$config=$config;
		if (@\is_array($config["namespaces"]))
			self::$namespaces=$config["namespaces"];
		\spl_autoload_register(array (__CLASS__,'autoload' ));
	}

	public static function tryToRequire($file) {
		if (\file_exists($file)) {
			require_once ($file);
			return true;
		}
		return false;
	}

	public static function autoload($class) {
		if (self::tryToRequire(ROOT . DS . \str_replace("\\", DS, $class) . '.php'))
			return;
		if (\is_array(self::$namespaces)) {
			$posSlash=\strrpos($class, '\\');
			$namespace=\substr($class, 0, $posSlash);
			if (isset(self::$namespaces[$namespace])) {
				$classname=\substr($class, $posSlash + 1);
				$classnameToDir=\str_replace("\\", DS, $namespace);
				self::tryToRequire(self::$namespaces[$namespace] . $classnameToDir . $classname . ".php");
			}
		}
	}
}
