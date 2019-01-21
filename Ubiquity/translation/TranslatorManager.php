<?php
namespace Ubiquity\translation;

use Ubiquity\translation\loader\ArrayLoader;
use Ubiquity\log\Logger;

/**
 * Manages translations
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 */
class TranslatorManager {
	protected static $locale;
	protected static $loader;
	protected static $catalogues;
	protected static $fallbackLocale;
	
	protected static function transCallable($callback,$id, array $parameters = array(), $domain = null, $locale = null){
		if (null === $domain) {
			$domain = 'messages';
		}
		$id = (string) $id;
		$catalogue = self::getCatalogue($locale);
		if($catalogue===false){
			if(isset(self::$fallbackLocale) && $locale!==self::$fallbackLocale){
				self::setLocale(self::$fallbackLocale);
				Logger::warn("Translation", "Locale ".$locale." not found, set active locale to ".self::$locale);
				return self::trans($id,$parameters,$domain,self::$locale);
			}else{
				Logger::error("Translation", "Locale not found, no valid fallbackLocale specified");
				return $id;
			}
		}
		$transId=self::getTransId($id, $domain);
		if(isset($catalogue[$transId])){
			return $callback($catalogue[$transId],$parameters);
		}elseif(self::$fallbackLocale!==null && $locale!==self::$fallbackLocale){
			Logger::warn("Translation", "Translation not found for ".$id.". Switch to fallbackLocale ".self::$fallbackLocale);
			return self::trans($id,$parameters,$domain,self::$fallbackLocale);
		}else{
			Logger::warn("Translation", "Translation not found for ".$id.". in locales.");
			return $id;
		}
	}
	
	protected static function replaceParams($trans,array $parameters=array()){
		foreach ($parameters as $k=>$v){
			$trans=str_replace('%'.$k.'%', $v, $trans);
		}
		return $trans;
	}
	
	protected static function getTransId($id,$domain){
		return $domain.".".$id;
	}
	
	protected static function assertValidLocale($locale){
		if (1 !== preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
			throw new \InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
		}
	}
	
	public static function start($locale="en_EN",$fallbackLocale=null,$rootDir=null){
		self::$locale=$locale;
		self::$fallbackLocale=$fallbackLocale;
		self::setRootDir($rootDir);
	}
	
	public static function setLocale($locale){
		self::assertValidLocale($locale);
		self::$locale = $locale;
	}
	
	public static function setRootDir($rootDir=null){
		if(!isset($rootDir)){
			$rootDir=\ROOT . \DS . "translations";
		}
		self::$loader=new ArrayLoader($rootDir);
	}
	
	public static function getLocale(){
		return self::$locale;
	}
	
	public static function trans($id, array $parameters = array(), $domain = null, $locale = null){
		return self::transCallable(function($catalog,$parameters){return self::replaceParams($catalog,$parameters);}, $id,$parameters,$domain,$locale);
	}
	
	public static function getCatalogue(&$locale = null){
		if (null === $locale) {
			$locale = self::getLocale();
		} else {
			self::assertValidLocale($locale);
		}
		if (!isset(self::$catalogues[$locale])) {
			self::loadCatalogue($locale);
		}
		return self::$catalogues[$locale];
	}
	
	public static function loadCatalogue($locale = null){
		self::$catalogues[$locale]=self::$loader->load($locale);
	}
	
	/**
	 * @return mixed
	 */
	public static function getFallbackLocale() {
		return self::$fallbackLocale;
	}

	/**
	 * @param mixed $fallbackLocale
	 */
	public static function setFallbackLocale($fallbackLocale) {
		self::$fallbackLocale = $fallbackLocale;
	}
	
	public static function clearCache(){
		self::$loader->clearCache();
	}
}
