<?php

namespace Ubiquity\utils;

use Ubiquity\controllers\Startup;

/**
 * Utilitaires liés à la requête $_POST ou $_GET
 * @author jc
 * @version 1.0.0.1
 */
class RequestUtils {

	/**
	 * Affecte membre à membre les valeurs du tableau associatif $values aux membres de l'objet $object
	 * Utilisé par exemple pour récupérer les variables postées et les affecter aux membres d'un objet
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
	public static function getInput(){
		$put = array();
		\parse_str(\file_get_contents('php://input'), $put);
		return $put;
	}

	/**
	 * Returns the query data, regardless of the method
	 * @return array
	 */
	public static function getDatas(){
		$method=\strtolower($_SERVER['REQUEST_METHOD']);
		switch ($method) {
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
	public static function getContentType(){
		$headers=getallheaders();
		if(isset($headers["content-type"])){
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
	 * Returns true if request contentType is set to json
	 * @return boolean
	 */
	public static function isJSON(){
		$contentType=self::getContentType();
		return \stripos($contentType, "json")!==false;
	}

	/**
	 * Retourne la valeur de la variable $key passée par la méthode get ou $default si la variable $key n'existe pas
	 * @param string $key
	 * @param string $default valeur retournée par défaut
	 * @return string
	 */
	public static function get($key, $default=NULL) {
		return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
	}

	/**
	 * Retourne la valeur de la variable $key passée par la méthode post ou $default si la variable $key n'existe pas
	 * @param string $key
	 * @param string $default valeur retournée par défaut
	 * @return string
	 */
	public static function post($key, $default=NULL) {
		return array_key_exists($key, $_POST) ? $_POST[$key] : $default;
	}

	public static function getUrl($url) {
		$config=Startup::getConfig();
		if (StrUtils::startswith($url, "/") === false) {
			$url="/" . $url;
		}
		return $config["siteUrl"] . $url;
	}

	public static function getUrlParts(){
		return \explode("/", $_GET["c"]);
	}

	public static function getMethod() {
		return \strtolower($_SERVER['REQUEST_METHOD']);
	}
}
