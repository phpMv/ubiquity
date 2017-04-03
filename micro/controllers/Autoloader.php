<?php
namespace micro\controllers;

/**
 * Classe Autoloader
 * @author jc
 * @version 1.0.0.1
 * @package controllers
 */
class Autoloader{

	public static function register(){
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
		global $config;
		$directories=array_merge(["controllers","models"],@$config["directories"]);
		$find=false;
		foreach ($directories as $directory){
			if($find=self::tryToRequire($directory,$class))
				break;
		}
		if($find===false){
			$namespaces=@$config["namespaces"];
			$posSlash=strrpos($class, '\\');
			$classname=substr($class,  $posSlash+ 1);
			$namespace=substr($class, 0, $posSlash);
			if(isset($namespaces[$namespace])){
				$directory=$namespaces[$namespace];
				if($find=self::tryToRequire($directory,$classname))
					break;
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
