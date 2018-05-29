<?php

namespace Ubiquity\utils\http;

/**
 * Http Cookies utilities
 * @author jc
 * @version 1.0.0.2
 */
class UCookie {

	/**
	 * Sends a cookie
	 * @param string $name the name of the cookie
	 * @param string $value The value of the cookie.
	 * @param int $duration default : 1 day
	 * @param string $path default : / the cookie will be available within the entire domain
	 */
	public static function set($name, $value, $duration=60*60*24, $path="/") {
		\setcookie($name, $value, \time() + $duration, $path);
	}

	/**
	 * Returns the Cookie with the name $name
	 * @param string $name
	 * @param string $default
	 * @return null|string
	 */
	public static function get($name, $default=null) {
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
	}

	/**
	 * Removes the cookie with the name $name
	 * @param string $name
	 * @param $path
	 */
	public static function delete($name, $path="/") {
		if(isset($_COOKIE[$name])){
			unset($_COOKIE[$name]);
		}
		\setcookie($name, "", \time() - 3600, $path);
	}

	/**
	 * Deletes all cookies
	 */
	public function deleteAll($path="/") {
		foreach ( $_COOKIE as $name => $value ) {
			self::delete($name, $path);
		}
	}
}
