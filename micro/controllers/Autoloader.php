<?php
namespace micro\controllers;

use micro\orm\OrmUtils;

/**
 * Classe Autoloader
 * @author jc
 * @version 1.0.0.2
 * @package controllers
 */
class Autoloader{
	private static $config;
	private static $directories;
	private static $namespaces;

	public static function register($config){
		self::$config=$config;
		if(@\is_array($config["namespaces"]))
			self::$namespaces=$config["namespaces"];
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	private static function tryToRequire($file){
		if(file_exists($file)){
			require_once($file);
			return true;
		}
		return false;
	}

	public static function autoload($class){
		$classname = \str_replace("\\",DS, $class);
		$find=self::tryToRequire(ROOT.DS.$classname.'.php');
		$posSlash=strrpos($class, '\\');
		$namespace=substr($class, 0, $posSlash);

		if($find===false && is_array(self::$namespaces)){
			$classname=substr($class,  $posSlash+ 1);
			if(isset(self::$namespaces[$namespace])){
				$classnameToDir = \str_replace("\\",DS, $namespace);
				$find=self::tryToRequire(self::$namespaces[$namespace].$classnameToDir.$classname.".php");
			}
		}
		if(substr($namespace, 0, strlen(self::$config["mvcNS"]["models"]))===self::$config["mvcNS"]["models"]){
			OrmUtils::createOrmModelCache($class);
		}
	}

}
