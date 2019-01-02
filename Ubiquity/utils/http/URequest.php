<?php

namespace Ubiquity\utils\http;

use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UString;

/**
 * Http Request utilities
 * @author jc
 * @version 1.0.1
 */
class URequest {

	/**
	 * Affects member to member the values of the associative array $values to the members of the object $object
	 * Used for example to retrieve the variables posted and assign them to the members of an object
	 * @param object $object
	 * @param associative array $values
	 */
	public static function setValuesToObject($object, $values=null) {
		if (!isset($values))
			$values=$_POST;
		foreach ( $values as $key => $value ) {
			$accessor="set" . ucfirst($key);
			if (method_exists($object, $accessor)) {
				$object->$accessor($value);
				$object->_rest[$key]=$value;
			}
		}
	}

	/**
	 * Affects member to member the values of $_GET to the members of the object $object
	 * $object must have accessors to each members
	 * @param object $object
	 */
	public static function setGetValuesToObject($object) {
		self::setValuesToObject($object,$_GET);
	}

	/**
	 * Affects member to member the values of $_POST to the members of the object $object
	 * $object must have accessors to each members
	 * @param object $object
	 */
	public static function setPostValuesToObject($object) {
		self::setValuesToObject($object,$_POST);
	}

	/**
	 * Call a cleaning function on the post
	 * @param string $function the cleaning function, default htmlentities
	 * @return array
	 */
	public static function getPost($function="htmlentities") {
		return array_map($function, $_POST);
	}

	/**
	 * Returns the query data, for PUT, DELETE PATCH methods
	 */
	public static function getInput() {
		$put=array ();
		\parse_str(\file_get_contents('php://input'), $put);
		return $put;
	}

	/**
	 * Returns the query data, regardless of the method
	 * @return array
	 */
	public static function getDatas() {
		$method=\strtolower($_SERVER['REQUEST_METHOD']);
		switch($method) {
			case 'post':
				return $_POST;
			case 'get':
				return $_GET;
			default:
				return self::getInput();
		}
	}

	/**
	 * Returns the request content-type header
	 * @return string
	 */
	public static function getContentType() {
		$headers=getallheaders();
		if (isset($headers["content-type"])) {
			return $headers["content-type"];
		}
		return null;
	}
	
	/**
	* Copyright © 2008 Darrin Yeager
	* https://www.dyeager.org/
	* Licensed under BSD license.
	* https://www.dyeager.org/downloads/license-bsd.txt
	**/
	public static function getDefaultLanguage() {
		if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
			return self::parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			else
				return self::parseDefaultLanguage(NULL);
	}
	
	private static function parseDefaultLanguage($http_accept, $deflang = "en") {
		if(isset($http_accept) && strlen($http_accept) > 1)  {
			$x = explode(",",$http_accept);
			$lang=[];
			foreach ($x as $val) {
				if(preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i",$val,$matches))
					$lang[$matches[1]] = (float)$matches[2];
					else
						$lang[$val] = 1.0;
			}
			
			$qval = 0.0;
			foreach ($lang as $key => $value) {
				if ($value > $qval) {
					$qval = (float)$value;
					$deflang = $key;
				}
			}
		}
		return $deflang;
	}
	
	public static function setLocale(string $locale){
		try {
			if (class_exists('Locale', false)) {
				\Locale::setDefault($locale);
			}
		} catch (\Exception $e) {
			//Nothing to do
		}
	}

	/**
	 * Returns true if the request is an Ajax request
	 * @return boolean
	 */
	public static function isAjax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	/**
	 * Returns true if the request is sent by the POST method
	 * @return boolean
	 */
	public static function isPost() {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * Returns true if the request is cross site
	 * @return boolean
	 */
	public static function isCrossSite() {
		return stripos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) === FALSE;
	}

	/**
	 * Returns true if request contentType is set to json
	 * @return boolean
	 */
	public static function isJSON() {
		$contentType=self::getContentType();
		return \stripos($contentType, "json") !== false;
	}

	/**
	 * Returns the value of the $key variable passed by the get method or $default if the $key variable does not exist
	 * @param string $key
	 * @param string $default return value by default
	 * @return string
	 */
	public static function get($key, $default=NULL) {
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}
	
	/**
	 * Returns a boolean at the key position in request
	 *
	 * @param string $key
	 *        	the key to add or set
	 * @return boolean
	 */
	public static function getBoolean($key) {
		$ret = false;
		if (isset ( $_REQUEST[$key] )) {
			$ret = UString::isBooleanTrue ( $_REQUEST[$key] );
		}
		return $ret;
	}

	/**
	 * Returns the value of the $key variable passed by the post method or $default if the $key variable does not exist
	 * @param string $key
	 * @param string $default return value by default
	 * @return string
	 */
	public static function post($key, $default=NULL) {
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	public static function getUrl($url) {
		$config=Startup::getConfig();
		$siteUrl=\rtrim($config["siteUrl"], '/');
		if (UString::startswith($url, "/") === false) {
			$url="/" . $url;
		}
		return $siteUrl . $url;
	}

	public static function getUrlParts() {
		return \explode("/", $_GET["c"]);
	}

	/**
	 * Returns the http method
	 * @return string
	 */
	public static function getMethod() {
		return \strtolower($_SERVER['REQUEST_METHOD']);
	}

	public static function cleanUrl($url){
		$url=\str_replace("\\", "/", $url);
		return \str_replace("//", "/", $url);
	}
}
