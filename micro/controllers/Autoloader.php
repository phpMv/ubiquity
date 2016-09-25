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

	public static function autoload($class){
		global $config;
		$find=false;
		if(file_exists(ROOT.DS."controllers".DS.$class.".php")){
			require_once(ROOT.DS."controllers".DS.$class.".php");
			$find=true;
		}
		else if(file_exists(ROOT.DS."models".DS.$class.".php")){
			require_once(ROOT.DS."models".DS.$class.".php");
			$find=true;
		}
		else if(file_exists(ROOT.DS."framework".DS.$class.".php")){
			require_once(ROOT.DS."framework".DS.$class.".php");
			$find=true;
		}
		else{
			foreach ($config["directories"] as $directory){
				if(file_exists(ROOT.DS.$directory.DS.$class.".php")){
					require_once(ROOT.DS.$directory.DS.$class.".php");
					$find=true;
					break;
				}
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