<?php

namespace Ubiquity\translation;

/**
 * Injectable translator (to use with di)
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 */
class Translator {
	private $manager;
	
	public function __construct($locale="en_EN",$fallbackLocale=null,$rootDir=null){
		$this->manager=new TranslatorManager();
		$this->manager->start($locale,$fallbackLocale,$rootDir);
	}
	
	public function setLocale($locale){
		$this->manager->setLocale($locale);
	}
	
	public function setRootDir($rootDir=null){
		$this->manager->setRootDir($rootDir);
	}
	
	public function getLocale(){
		return $this->manager->getLocale();
	}
	
	public function trans($id, array $parameters = array(), $domain = null, $locale = null){
		return $this->manager->trans($id,$parameters,$domain,$locale);
	}
	
	public function getCatalogue(&$locale = null){
		return $this->manager->getCatalogue($locale);
	}
	
	public function loadCatalogue($locale = null){
		$this->manager->loadCatalogue($locale);
	}
	
	/**
	 * @return mixed
	 */
	public function getFallbackLocale() {
		return $this->manager->getFallbackLocale();
	}
	
	/**
	 * @param mixed $fallbackLocale
	 */
	public function setFallbackLocale($fallbackLocale) {
		$this->manager->setFallbackLocale($fallbackLocale);
	}
	
	public function clearCache(){
		$this->manager->clearCache();
	}
}

