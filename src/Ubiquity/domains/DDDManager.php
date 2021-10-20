<?php


namespace Ubiquity\domains;

use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UFileSystem;

/**
 * Manager for a Domain Driven Design approach.
 * Ubiquity\domains$DDDManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 0.0.0
 *
 */
class DDDManager {
	private static $base='domains';
	private static $activeDomain='';

	public static function start(string $base='domains'): void{
		self::$base=$base;
	}

	public static function setDomain(string $domain): void {
		self::$activeDomain=$domain;
		Startup::setActiveDomainBase($domain,self::$base);
	}

	public static function resetActiveDomain(): void {
		self::$activeDomain='';
		Startup::resetActiveDomainBase();
	}

	public static function getDomains(): array {
		return \array_map('basename', \glob(\ROOT.self::$base . '/*' , \GLOB_ONLYDIR));
	}

	public static function hasDomains(): bool {
		return \file_exists(\ROOT.self::$base) && \count(self::getDomains())>0;
	}
	
	public static function getActiveDomain(): string {
		return self::$activeDomain;
	}
	
	public static function getActiveViewFolder(){
		if(self::$activeDomain!=''){
			if(\file_exists($folder=\ROOT.self::$base.\DS.self::$activeDomain.\DS.'views'.\DS)){
				return $folder;
			}
		}
		return \ROOT.'views'.\DS;
	}
	
	public static function getViewNamespace(){
		$activeDomain=self::$activeDomain;
		if($activeDomain!=''){
			return '@'.$activeDomain.'/';
		}
		return '';		
	}
	
	public static function getDomainBase($domain){
		return self::$base.\DS. \trim($domain, '\\') . '\\';
	}

	public static function createDomain($domainName){
		$baseFolder=$folder=\ROOT.self::$base.\DS.$domainName.\DS;
		$result=self::createFolder($baseFolder.'views');
		if($result) {
			$result = self::createFolder($baseFolder . (Startup::$config['mvcNS']['controllers']) ?? 'controllers');
			if($result){
				$result=self::createFolder($baseFolder . (Startup::$config['mvcNS']['models']) ?? 'models');
			}
		}
		return $result;
	}

	private static function createFolder(string $folder): bool {
		if(UFileSystem::safeMkdir($folder)){
			return false!==\file_put_contents($folder.\DS.'.gitkeep','');
		}
		return false;
	}

	/**
	 * @return string
	 */
	public static function getBase(): string {
		return self::$base;
	}
	
}