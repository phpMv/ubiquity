<?php

namespace Ubiquity\translation;

use Ubiquity\translation\loader\ArrayLoader;
use Ubiquity\log\Logger;

class Translator {
	protected $locale;
	protected $loader;
	protected $catalogues;
	protected $fallbackLocale;
	
	public function __construct($locale="en_EN"){
		$this->locale=$locale;
		$this->loader=new ArrayLoader();
	}
	
	public function setLocale($locale){
		$this->assertValidLocale($locale);
		$this->locale = $locale;
	}
	
	
	
	public function getLocale(){
		return $this->locale;
	}
	
	public function trans($id, array $parameters = array(), $domain = null, $locale = null){
		if (null === $domain) {
			$domain = 'messages';
		}
		$id = (string) $id;
		$catalogue = $this->getCatalogue($locale);
		if($catalogue===false){
			if(isset($this->fallbackLocale) && $locale!==$this->fallbackLocale){
				$this->setLocale($this->fallbackLocale);
				Logger::warn("Translation", "Locale ".$locale." not found, set active locale to ".$this->locale);
				return $this->trans($id,$parameters,$domain,$this->locale);
			}else{
				Logger::error("Translation", "Locale not found, no valid fallbackLocale specified");
				return $id;
			}
		}
		$transId=$this->getTransId($id, $domain);
		if(isset($catalogue[$transId])){
			return $this->doTrans($catalogue[$transId]);
		}elseif($this->fallbackLocale!==null && $locale!==$this->fallbackLocale){
			Logger::warn("Translation", "Translation not found for ".$id.". Switch to fallbackLocale ".$this->fallbackLocale);
			return $this->trans($id,$parameters,$domain,$this->fallbackLocale);
		}else{
			Logger::warn("Translation", "Translation not found for ".$id.". in locales.");
			return $id;
		}
	}
	
	protected function doTrans($trans,array $parameters=array()){
		foreach ($parameters as $k=>$v){
			$trans=str_replace('%'.$k.'%', $v, $trans);
		}
		return $trans;
	}
	
	protected function getTransId($id,$domain){
		return $domain.".".$id;
	}
	
	
	public function getCatalogue(&$locale = null){
		if (null === $locale) {
			$locale = $this->getLocale();
		} else {
			$this->assertValidLocale($locale);
		}
		if (!isset($this->catalogues[$locale])) {
			$this->loadCatalogue($locale);
		}
		return $this->catalogues[$locale];
	}
	
	public function loadCatalogue($locale = null){
		$this->catalogues[$locale]=$this->loader->load($locale);
	}
	
	protected function assertValidLocale($locale){
		if (1 !== preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
			throw new \InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
		}
	}
	/**
	 * @return mixed
	 */
	public function getFallbackLocale() {
		return $this->fallbackLocale;
	}

	/**
	 * @param mixed $fallbackLocale
	 */
	public function setFallbackLocale($fallbackLocale) {
		$this->fallbackLocale = $fallbackLocale;
	}
	
	public function clearCache(){
		apc_clear_cache('user');
	}

}

