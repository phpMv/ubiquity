<?php

namespace Ubiquity\utils\http;

use Ubiquity\contents\transformation\TransformerInterface;

/**
 * Http Cookies utilities
 * Ubiquity\utils\http$UCookie
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.4
 *
 */
class UCookie {

	/**
	 *
	 * @var TransformerInterface
	 */
	private static $transformer;
	public static $useTransformer = false;

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
	public static function set($name, $value, $duration = 60 * 60 * 24, $path = '/', $secure = false, $httpOnly = false): bool {
		if ($value!=null && self::$useTransformer && isset ( self::$transformer )) {
			$value = self::$transformer->transform ( $value );
		}
		return \setcookie ( $name, $value, $duration ? (\time () + $duration) : 0, $path, $secure, $httpOnly );
	}

	/**
	 * Returns the Cookie with the name $name
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($name, $default = null) {
		$v = $_COOKIE [$name] ?? $default;
		if ($v!=null && self::$useTransformer && isset ( self::$transformer )) {
			return self::$transformer->reverse ( rawurldecode($v) );
		}
		return $v;
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
		foreach ( $_COOKIE as $name => $_ ) {
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
	 * @param boolean $secure Indicates that the cookie should only be transmitted over a secure HTTPS
	 * @param boolean $httpOnly When true the cookie will be made accessible only through the HTTP protocol
	 * @return boolean
	 * @since Ubiquity 2.0.11
	 */
	public static function setRaw($name, $value, $duration = 60 * 60 * 24, $path = '/', $secure = false, $httpOnly = false): bool {
		if ($value!=null && self::$useTransformer && isset ( self::$transformer )) {
			$value = self::$transformer->transform ( $value );
		}
		return \setrawcookie ( $name, $value, \time () + $duration, $path, $secure, $httpOnly );
	}

	/**
	 * Gets a specific cookie by name and optionally filters it
	 * @param string $key
	 * @param int $filter
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public static function filter(string $key,int $filter=\FILTER_DEFAULT,$default=null){
		return \filter_input(INPUT_COOKIE,$key,$filter)??$default;
	}

	public static function setTransformer(TransformerInterface $transformer) {
		self::$transformer = $transformer;
		self::$useTransformer = true;
	}

	public static function getTransformerClass(): ?string {
		if (isset ( self::$transformer )) {
			return \get_class ( self::$transformer );
		}
		return null;
	}
}
