<?php
namespace micro\controllers;

/**
 * Classe Autoloader
 * @author jc
 * @version 1.0.0.2
 * @package controllers
 */
class Autoloader{
	private static $config;
	private static $directories;
	public static function register($config){
		self::$config=$config;
		self::$directories=["controllers","models"];
		if(is_array($config["directories"])){
			self::$directories=array_merge(self::$directories,$config["directories"]);
		}
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}
	
	private static function tryToRequire($directory,$class){
		if(file_exists(ROOT.DS.$directory.DS.$class.".php")){
			require_once(ROOT.DS.$directory.DS.$class.".php");
			return true;
		}
		return false;
	}

	public static function autoload($class){
		$config=self::$config;
		$find=false;
		foreach (self::$directories as $directory){
			if($find=self::tryToRequire($directory,$class))
				break;
		}
		if($find===false && is_array($config["namespaces"])){
			$namespaces=$config["namespaces"];
			$posSlash=strrpos($class, '\\');
			$classname=substr($class,  $posSlash+ 1);
			$namespace=substr($class, 0, $posSlash);
			if(isset($namespaces[$namespace])){
				$find=self::tryToRequire($namespaces[$namespace],$classname);
			}
		}
		if($find===false){
			$nameSpace = explode('\\', $class);
			foreach($nameSpace as $key =>  $value){
				$keys=array_keys($nameSpace);
				if(end($keys) !== $key){
					$nameSpace[$key] = strtolower($value);
				}
			}
			$class = implode(DS, $nameSpace);

			if(strstr($class,"micro".DS)===false){
				if(file_exists($class.'.php'))
				require $class.'.php';
			}
			else
				require ROOT.DS.$class.'.php';
		}
	}

}
