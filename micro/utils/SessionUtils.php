<?php
namespace micro\utils;
/**
 * Utilitaires liés à la session
 * @author jc
 * @version 1.0.0.1
 * @package utils
 */
class SessionUtils{

	/**
	 * Retourne un tableau stocké en variable de session sous le nom $arrayKey
	 * @param string $arrayKey
	 * @return array
	 */
	public static function getArray($arrayKey){
		if(array_key_exists($arrayKey,$_SESSION)){
			$array=$_SESSION[$arrayKey];
			if(!is_array($array))
				$array=array();
		}else
			$array=array();
		return $array;
	}

	public static function addOrRemoveValueFromArray($arrayKey,$value){
		$array=SessionUtils::getArray($arrayKey);
		$_SESSION[$arrayKey]=$array;
		$search=array_search($value, $array);
		if($search===FALSE){
			$_SESSION[$arrayKey][]=$value;
			return true;
		}else{
			unset($_SESSION[$arrayKey][$search]);
			$_SESSION[$arrayKey] = array_values($_SESSION[$arrayKey]);
			return false;
		}
	}

	public static function removeValueFromArray($arrayKey,$value){
		$array=SessionUtils::getArray($arrayKey);
		$_SESSION[$arrayKey]=$array;
		$search=array_search($value, $array);
		if($search!==FALSE){
			unset($_SESSION[$arrayKey][$search]);
			$_SESSION[$arrayKey] = array_values($_SESSION[$arrayKey]);
			return true;
		}
		return false;
	}

	public static function checkBoolean($key){
		$_SESSION[$key]=!SessionUtils::getBoolean($key);
		return $_SESSION[$key];
	}

	public static function getBoolean($key){
		$ret=false;
		if(array_key_exists($key,$_SESSION)){
			$ret=$_SESSION[$key];
		}
		return $ret;
	}

	public static function session($key, $default=NULL) {
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
	}

	public static function delete($key){
		unset($_SESSION[$key]);
	}
}