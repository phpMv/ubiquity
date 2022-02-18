<?php

namespace Ubiquity\utils\http;

use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\traits\URequestTesterTrait;

/**
 * Http Request utilities, wrapper for accessing to $_GET, $_POST and php://input.
 * Ubiquity\utils\http$URequest
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.4
 *
 */
class URequest {
use URequestTesterTrait;

	/**
	 * @var array
	 */
	private static $uriInfos;
	/**
	 * Affects member to member the values of the associative array $values to the members of the object $object
	 * Used for example to retrieve the variables posted and assign them to the members of an object
	 *
	 * @param object $object
	 * @param array $values
	 */
	public static function setValuesToObject($object, $values = null): void {
		if (! isset ( $values )) {
			$values = $_POST;
		}
		foreach ( $values as $key => $value ) {
			$accessor = 'set' . \ucfirst ( $key );
			if (\method_exists ( $object, $accessor )) {
				$object->$accessor ( $value );
				$object->_rest [$key] = $value;
			}
		}
	}

	/**
	 * Affects member to member the values of $_GET to the members of the object $object
	 * $object must have accessors to each members
	 *
	 * @param object $object
	 */
	public static function setGetValuesToObject($object): void {
		self::setValuesToObject ( $object, $_GET );
	}

	/**
	 * Affects member to member the values of $_POST to the members of the object $object
	 * $object must have accessors to each members
	 *
	 * @param object $object
	 */
	public static function setPostValuesToObject($object): void {
		self::setValuesToObject ( $object, $_POST );
	}

	/**
	 * Call a cleaning function on the post
	 *
	 * @param string $function the cleaning function, default htmlentities
	 * @return array
	 */
	public static function getPost($function = 'htmlentities'): array {
		return \array_map ( $function, $_POST );
	}

	/**
	 * Returns the query data, for PUT, DELETE PATCH methods
	 */
	public static function getInput(): array {
		return Startup::getHttpInstance ()->getInput ();
	}

	/**
	 * Returns the query data, regardless of the method
	 *
	 * @return array
	 */
	public static function getDatas(): array {
		$method = \strtolower ( $_SERVER ['REQUEST_METHOD'] );
		switch ($method) {
			case 'post' :
				if (self::getContentType () == 'application/x-www-form-urlencoded') {
					return $_POST;
				}
				break;
			case 'get' :
				return $_GET;
			default :
				return self::getInput ();
		}
		return self::getInput ();
	}


	/**
	 * Adds a value in request at $key position
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	public static function set(string $key, $value = true) {
		return $_REQUEST [$key] = $value;
	}

	/**
	 * Copyright © 2008 Darrin Yeager
	 * https://www.dyeager.org/
	 * Licensed under BSD license.
	 * https://www.dyeager.org/downloads/license-bsd.txt
	 */
	public static function getDefaultLanguage(): string {
		if (isset ( $_SERVER ['HTTP_ACCEPT_LANGUAGE'] )) {
			return self::parseDefaultLanguage ( $_SERVER ['HTTP_ACCEPT_LANGUAGE'] );
		}
		return self::parseDefaultLanguage ( NULL );
	}

	private static function parseDefaultLanguage($http_accept, $deflang = 'en'): string {
		if (isset ( $http_accept ) && \strlen ( $http_accept ) > 1) {
			$x = \explode ( ",", $http_accept );
			$lang = [ ];
			foreach ( $x as $val ) {
				if (\preg_match ( "/(.*);q=([0-1]{0,1}.\d{0,4})/i", $val, $matches ))
					$lang [$matches [1]] = ( float ) $matches [2];
				else
					$lang [$val] = 1.0;
			}

			$qval = 0.0;
			foreach ( $lang as $key => $value ) {
				if ($value > $qval) {
					$qval = ( float ) $value;
					$deflang = $key;
				}
			}
		}
		return $deflang;
	}

	public static function setLocale(string $locale): void {
		try {
			if (\class_exists ( 'Locale', false )) {
				\Locale::setDefault ( $locale );
			}
		} catch ( \Exception $e ) {
			// Nothing to do
		}
	}


	/**
	 * Returns the value of the $key variable passed by the get method or $default if the $key variable does not exist
	 *
	 * @param string $key
	 * @param string $default return value by default
	 * @return string
	 */
	public static function get($key, $default = NULL): ?string {
		return $_GET [$key] ?? $default;
	}

	/**
	 * Returns a boolean at the key position in request
	 *
	 * @param string $key the key to add or set
	 * @return boolean
	 */
	public static function getBoolean($key): bool {
		$ret = false;
		if (isset ( $_REQUEST [$key] )) {
			$ret = UString::isBooleanTrue ( $_REQUEST [$key] );
		}
		return $ret;
	}

	/**
	 * Returns the value of the $key variable passed by the post method or $default if the $key variable does not exist
	 *
	 * @param string $key
	 * @param string $default return value by default
	 * @return mixed
	 */
	public static function post($key, $default = NULL) {
		return $_POST [$key] ?? $default;
	}

	public static function getUrl($url): string {
		$config = Startup::getConfig ();
		$siteUrl = \rtrim ( $config ['siteUrl'], '/' );
		if (UString::startswith ( $url, '/' ) === false) {
			$url = '/' . $url;
		}
		return $siteUrl . $url;
	}

	public static function getUrlParts(): array {
		return \explode ( '/', $_GET ['c'] );
	}

	/**
	 * Returns the http method
	 *
	 * @return string
	 */
	public static function getMethod(): string {
		return \strtolower ( $_SERVER ['REQUEST_METHOD'] );
	}

	/**
	 * Returns the request origin
	 *
	 * @return string
	 */
	public static function getOrigin(): string {
		$headers = Startup::getHttpInstance ()->getAllHeaders ();
		if (isset ( $headers ['Origin'] )) {
			return $headers ['Origin'];
		}
		if (isset ( $_SERVER ['HTTP_ORIGIN'] )) {
			return $_SERVER ['HTTP_ORIGIN'];
		} else if (isset ( $_SERVER ['HTTP_REFERER'] )) {
			return $_SERVER ['HTTP_REFERER'];
		} else {
			return $_SERVER ['REMOTE_ADDR'];
		}
	}

	public static function cleanUrl($url): string {
		$url = \str_replace ( "\\", "/", $url );
		return \str_replace ( "//", "/", $url );
	}

	/**
	 * Fix up PHP's messing up input containing dots, etc.
	 * `$source` can be either 'post' or 'get'
	 *
	 * @param string $source
	 * @return string[]
	 * @see https://stackoverflow.com/questions/68651/can-i-get-php-to-stop-replacing-characters-in-get-or-post-arrays#68667
	 */
	public static function getRealInput($source = 'post'): array {
		$pairs = \explode ( '&', \strtolower ( $source ) === 'get' ? $_SERVER ['QUERY_STRING'] : \file_get_contents ( 'php://input' ) );
		$vars = array ();
		foreach ( $pairs as $pair ) {
			$nv = \explode ( "=", $pair );
			$name = \urldecode ( $nv [0] );
			$value = \urldecode ( $nv [1] ?? '');
			$vars [$name] = $value;
		}
		return $vars;
	}

	public static function getRealGET(): array {
		return self::getRealInput ( 'get' );
	}

	public static function getRealPOST(): array {
		return self::getRealInput ( 'post' );
	}

	/**
	 * Creates a password hash for a posted value at $key position
	 *
	 * @param string $key
	 * @param string $algo
	 * @return string|boolean
	 */
	public static function password_hash(string $key, string $algo = PASSWORD_DEFAULT) {
		if (isset ( $_POST [$key] )) {
			return $_POST [$key] = \password_hash ( $_POST [$key], $algo );
		}
		return false;
	}


	/**
	 * Verifies that a posted password matches a hash at $passwordKey position.
	 *
	 * @param string $passwordKey
	 * @param string $hash
	 * @return bool
	 * @since 2.4.0
	 */
	public static function password_verify(string $passwordKey,string $hash):bool {
		if (isset ( $_POST [$passwordKey] )) {
			return \password_verify( $_POST [$passwordKey], $hash );
		}
		return false;
	}

	/**
	 * Internal use for async servers (Swoole and Workerman).
	 * @param string $uri
	 * @param string $basedir
	 * @return array
	 */
	public static function parseURI(string $uri,string $basedir):array {
		return self::$uriInfos[$uri]??=self::_parseURI($uri,$basedir);
	}

	/**
	 * Gets a specific external variable by name and optionally filters it
	 * @param string $key
	 * @param int $type
	 * @param int $filter
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public static function filter(string $key,int $type=\INPUT_POST,int $filter=\FILTER_DEFAULT,$default=null){
		return \filter_input($type,$key,$filter)??$default;
	}

	/**
	 * Gets a specific POST variable by name and optionally filters it
	 * @param string $key
	 * @param int $filter
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public static function filterPost(string $key,int $filter=\FILTER_DEFAULT,$default=null){
		return self::filter($key,INPUT_POST,$filter,$default);
	}

	/**
	 * Gets a specific GET variable by name and optionally filters it
	 * @param string $key
	 * @param int $filter
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public static function filterGet(string $key,int $filter=\FILTER_DEFAULT,$default=null){
		return self::filter($key,INPUT_GET,$filter,$default);
	}
	
	private static function _parseURI(string $uri,string $basedir):array {
		$uri = \ltrim(\urldecode(\parse_url($uri, PHP_URL_PATH)), '/');
		$isAction = ($uri == null || ! ($fe = \file_exists($basedir . '/../' . $uri))) && ($uri != 'favicon.ico');
		return [
				'uri' => $uri,
				'isAction' => $isAction,
				'file' => $fe??false
		];
	}
}
