<?php
namespace micro\utils;

/**
 * Utilitaires liés à la requête $_POST ou $_GET
 * @author jc
 * @version 1.0.0.1
 * @package utils
 */
class RequestUtils{
	/**
	 * Affecte membre à membre les valeurs du tableau associatif $values aux membres de l'objet $object
	 * Utilisé par exemple pour récupérer les variables postées et les affecter aux membres d'un objet
	 * @param Class $object
	 * @param associative array $values
	 */
	public static function setValuesToObject($object,$values=null){
		if(!isset($values))
			$values=$_POST;
		foreach ($values as $key=>$value){
			$accessor="set".ucfirst($key);
			if(method_exists($object, $accessor)){
				$object->$accessor($value);
			}
		}
	}
	/**
	 * Appel d'une fonction de nettoyage sur le post
	 * @param string $function
	 * @return multitype:
	 */
	public static function getPost($function="htmlentities"){
		return array_map($function, $_POST);
	}

	/**
	 * Retourne vrai si la requête est une requête Ajax
	 * @return boolean
	 */
	public static function isAjax(){
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	/**
	 * Retourne vrai si la requête est envoyée par la méthode POST
	 * @return boolean
	 */
	public static function isPost(){
		return $_SERVER['REQUEST_METHOD']==='POST';
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

	public static function getUrl($url){
		$config=$GLOBALS["config"];
		if(StrUtils::startswith($url, "/")==false){
			$url="/".$url;
		}
		return $config["siteUrl"].$url;
	}
}