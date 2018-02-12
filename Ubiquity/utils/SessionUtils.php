<?php

namespace Ubiquity\utils;

/**
 * Session utilities
 * @author jc
 * @version 1.0.0.2
 */
class SessionUtils {

	/**
	 * Retourne un tableau stocké en variable de session sous le nom $arrayKey
	 * @param string $arrayKey
	 * @return array
	 */
	public static function getArray($arrayKey) {
		if (isset($_SESSION[$arrayKey])) {
			$array=$_SESSION[$arrayKey];
			if (!is_array($array))
				$array=array ();
		} else
			$array=array ();
		return $array;
	}

	public static function addOrRemoveValueFromArray($arrayKey, $value, $add=true) {
		$array=self::getArray($arrayKey);
		$_SESSION[$arrayKey]=$array;
		$search=array_search($value, $array);
		if ($search === FALSE && $add) {
			$_SESSION[$arrayKey][]=$value;
			return true;
		} else {
			unset($_SESSION[$arrayKey][$search]);
			$_SESSION[$arrayKey]=array_values($_SESSION[$arrayKey]);
			return false;
		}
	}

	public static function removeValueFromArray($arrayKey, $value) {
		return self::addOrRemoveValueFromArray($arrayKey, $value, false);
	}

	public static function checkBoolean($key) {
		$_SESSION[$key]=!self::getBoolean($key);
		return $_SESSION[$key];
	}

	public static function getBoolean($key) {
		$ret=false;
		if (isset($_SESSION[$key])) {
			$ret=$_SESSION[$key];
		}
		return $ret;
	}

	public static function session($key, $default=NULL) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	public static function get($key, $default=NULL) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	public static function set($key, $value) {
		$_SESSION[$key]=$value;
	}

	public static function delete($key) {
		unset($_SESSION[$key]);
	}

	public static function start($name=null){
		if(isset($name)) \session_name();
		if (!isset($_SESSION)) { \session_start(); }
	}

	public static function isStarted(){
		return isset($_SESSION);
	}

	public static function terminate(){
		if(!self::isStarted())
			return;
		$_SESSION = array();

		if (\ini_get("session.use_cookies")) {
			$params = \session_get_cookie_params();
			\setcookie(\session_name(), '', \time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
					);
		}
		\session_destroy();
	}
}
