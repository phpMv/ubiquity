<?php

namespace Ubiquity\utils\http;

use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UString;

/**
 * Http Request utilities
 * @author jc
 * @version 1.0.0.2
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
