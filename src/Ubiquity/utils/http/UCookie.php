<?php

namespace Ubiquity\utils\http;

/**
 * Http Cookies utilities
 * Ubiquity\utils\http$UCookie
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 *
 */
class UCookie {

	/**
	 * Sends a cookie
	 *
	 * @param string $name the name of the cookie
	 * @param string $value The value of the cookie.
	 * @param int $duration default : 1 day
	 * @param string $path default : / the cookie will be available within the entire domain
	 * @param boolean $secure Indicates that the cookie should only be transmitted over asecure HTTPS
	 * @param boolean $httpOnly When true the cookie will be made accessible only through the HTTPprotocol
	 * @return boolean
	 */
	public static function set($name, $value, $duration = 60*60*24, $path = '/', $secure = false, $httpOnly = false): bool {
		return \setcookie ( $name, $value, \time () + $duration, $path, $secure, $httpOnly );
	}

	/**
	 * Returns the Cookie with the name $name
	 *
	 * @param string $name
	 * @param string $default
	 * @return ?string
	 */
	public static function get($name, $default = null): ?string {
		return $_COOKIE [$name] ?? $default;
	}

	/**
	 * Removes the cookie with the name $name
	 *
	 * @param string $name
	 * @param string $path
	 */
	public static function delete($name, $path = '/'): bool {
		if (isset ( $_COOKIE [$name] )) {
			unset ( $_COOKIE [$name] );
		}
		return \setcookie ( $name, '', \time () - 3600, $path );
	}

	/**
	 * Deletes all cookies
	 */
	public static function deleteAll($path = '/'): void {
		foreach ( $_COOKIE as $name => $value ) {
			self::delete ( $name, $path );
		}
	}

	/**
	 * Tests the existence of a cookie
	 *
	 * @param string $name
	 * @return boolean
	 * @since Ubiquity 2.0.11
	 */
	public static function exists($name): bool {
		return isset ( $_COOKIE [$name] );
	}

	/**
	 * Sends a raw cookie without urlencoding the cookie value
	 *
	 * @param string $name the name of the cookie
	 * @param string $value The value of the cookie.
	 * @param int $duration default : 1 day
	 * @param string $path default : / the cookie will be available within the entire domain
	 * @param boolean $secure Indicates that the cookie should only be transmitted over asecure HTTPS
	 * @param boolean $httpOnly When true the cookie will be made accessible only through the HTTPprotocol
	 * @return boolean
	 * @since Ubiquity 2.0.11
	 */
	public static function setRaw($name, $value, $duration = 60*60*24, $path = '/', $secure = false, $httpOnly = false): bool {
		return \setrawcookie ( $name, $value, \time () + $duration, $path, $secure, $httpOnly );
	}
}
