<?php

namespace Ubiquity\utils;

/**
 * Session utilities
 * @author jc
 * @version 1.0.0.3
 */
class SessionUtils {
	private static $name;

	/**
	 * Returns an array stored in session variable as $arrayKey
	 * @param string $arrayKey the key of the array to return
	 * @return array
	 */
	public static function getArray($arrayKey) {
		self::start();
		if (isset($_SESSION[$arrayKey])) {
			$array=$_SESSION[$arrayKey];
			if (!is_array($array))
				$array=[ ];
		} else
			$array=[ ];
		return $array;
	}

	/**
	 * Adds or removes a value from an array in session
	 * @param string $arrayKey the key of the array to add or remove in
	 * @param mixed $value the value to add
	 * @param boolean $add If true, adds otherwise removes
	 * @return boolean
	 */
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

	/**
	 * Removes a value from an array in session
	 * @param string $arrayKey the key of the array to remove in
	 * @param mixed $value the value to remove
	 * @return boolean
	 */
	public static function removeValueFromArray($arrayKey, $value) {
		return self::addOrRemoveValueFromArray($arrayKey, $value, false);
	}

	/**
	 * Adds a value from an array in session
	 * @param string $arrayKey the key of the array to add in
	 * @param mixed $value the value to add
	 * @return boolean
	 */
	public static function addValueToArray($arrayKey, $value) {
		return self::addOrRemoveValueFromArray($arrayKey, $value, true);
	}

	/**
	 * Sets a boolean value at key position in session
	 * @param string $key the key to add or set in
	 * @param mixed $value the value to set
	 * @return boolean
	 */
	public static function setBoolean($key, $value) {
		$_SESSION[$key]=StrUtils::isBooleanTrue($value);
		return $_SESSION[$key];
	}

	/**
	 * Returns a boolean stored at the key position in session
	 * @param string $key the key to add or set
	 * @return boolean
	 */
	public static function getBoolean($key) {
		self::start();
		$ret=false;
		if (isset($_SESSION[$key])) {
			$ret=StrUtils::isBooleanTrue($_SESSION[$key]);
		}
		return $ret;
	}

	/**
	 * Returns the value stored at the key position in session
	 * @param string $key the key to retreive
	 * @param mixed $default the default value to return if the key does not exists in session
	 * @return mixed
	 */
	public static function session($key, $default=NULL) {
		self::start();
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/**
	 * Returns the value stored at the key position in session
	 * @param string $key the key to retreive
	 * @param mixed $default the default value to return if the key does not exists in session
	 * @return mixed
	 */
	public static function get($key, $default=NULL) {
		self::start();
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/**
	 * Adds or sets a value to the Session at position $key
	 * @param string $key the key to add or set
	 * @param mixed $value
	 */
	public static function set($key, $value) {
		$_SESSION[$key]=$value;
	}

	/**
	 * Deletes the key in Session
	 * @param string $key the key to delete
	 */
	public static function delete($key) {
		self::start();
		unset($_SESSION[$key]);
	}

	/**
	 * Start new or resume existing session
	 * @param string|null $name the name of the session
	 */
	public static function start($name=null) {
		if (!isset($_SESSION)) {
			if (isset($name) && $name !== "") {
				self::$name=$name;
			}
			if (isset(self::$name)) {
				\session_name(self::$name);
			}
			\session_start();
		}
	}

	/**
	 * Returns true if the session is started
	 * @return boolean
	 */
	public static function isStarted() {
		return isset($_SESSION);
	}

	/**
	 * Returns true if the key exists in Session
	 * @param string $key the key to test
	 * @return boolean
	 */
	public static function exists($key) {
		self::start();
		return isset($_SESSION[$key]);
	}

	/**
	 * Terminates the active session
	 */
	public static function terminate() {
		if (!self::isStarted())
			return;
		self::start();
		$_SESSION=array ();

		if (\ini_get("session.use_cookies")) {
			$params=\session_get_cookie_params();
			\setcookie(\session_name(), '', \time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		\session_destroy();
	}
}
