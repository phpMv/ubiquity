<?php


namespace Ubiquity\domains;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\base\UString;

/**
 * Manager for a Domain Driven Design approach.
 * Ubiquity\domains$DDDManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 0.0.1
 *
 */
class DDDManager {
	private static string $base='domains';
	private static string $activeDomain='';
	
	private static function getNamespace(string $type='controllers'): string{
		$prefix='';
		if(self::$activeDomain!='') {
			$prefix = self::$base . '\\' . self::$activeDomain . '\\';
		}
		return $prefix.((Startup::$config['mvcNS'][$type]) ?? $type);
	}
	
	/**
	 * Starts the domain manager.
	 * To use only if the domain base is different from domains.
	 */
	public static function start(): void{
		self::$base=Startup::$config['mvcNS']['domains']??'domains';
	}
	
	/**
	 * Sets the active domain.
	 *
	 * @param string $domain
	 */
	public static function setDomain(string $domain): void {
		self::$activeDomain = $domain;
		Startup::setActiveDomainBase($domain, self::$base);
	}
	
	/**
	 * Removes the active domain.
	 */
	public static function resetActiveDomain(): void {
		self::$activeDomain='';
		Startup::resetActiveDomainBase();
	}
	
	/**
	 * Returns an array of existing domains.
	 *
	 * @return array
	 */
	public static function getDomains(): array {
		return \array_map('basename', \glob(\ROOT.self::$base . '/*' , \GLOB_ONLYDIR));
	}
	
	/**
	 * Check if there are any domains.
	 * @return bool
	 */
	public static function hasDomains(): bool {
		return \file_exists(\ROOT.self::$base) && \count(self::getDomains())>0;
	}
	
	/**
	 * Check if the domain exist.
	 *
	 * @param string $domain
	 * @return bool
	 */
	public static function domainExists(string $domain): bool {
		$domains=self::getDomains();
		return \array_search($domain,$domains)!==false;
	}
	
	/**
	 * Returns the active domain name.
	 * @return string
	 */
	public static function getActiveDomain(): string {
		return self::$activeDomain;
	}
	
	/**
	 * Returns the active view folder.
	 *
	 * @return string
	 */
	public static function getActiveViewFolder(): string {
		if(self::$activeDomain != '' && \file_exists($folder = \ROOT . self::$base . \DS . self::$activeDomain . \DS . 'views' . \DS)) {
			return $folder;
		}
		return \ROOT.'views'.\DS;
	}
	
	/**
	 * Returns the active view namespace.
	 *
	 * @return string
	 */
	public static function getViewNamespace(): string {
		if(($activeDomain=self::$activeDomain)!=''){
			return '@'.$activeDomain.'/';
		}
		return '';
	}
	
	/**
	 * Returns the base folder for a domain.
	 *
	 * @param string $domain
	 * @return string
	 */
	public static function getDomainBase(string $domain): string {
		return self::$base.\DS. \trim($domain, '\\') . '\\';
	}
	
	/**
	 * Creates a new domain.
	 *
	 * @param string $domainName
	 * @return bool
	 */
	public static function createDomain(string $domainName): bool {
		$baseFolder=\ROOT.self::$base.\DS.$domainName.\DS;
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
	
	private static function updateClassesNamespace(string $oldBase,string $newBase): void {
		$files=UFileSystem::glob_recursive(\ROOT.$newBase.\DS.'*.{php}',GLOB_BRACE);
		foreach ($files as $file){
			if(($content=\file_get_contents($file))!==false){
				$content=\str_replace($oldBase.'\\',$newBase.'\\',$content);
				\file_put_contents($file,$content);
			}
		}
	}
	
	
	/**
	 * Returns the domains base directory.
	 *
	 * @return string
	 */
	public static function getBase(): string {
		return self::$base;
	}
	
	/**
	 * Changes the base directory for domains.
	 * Do not use in production!
	 * @param string $base
	 * @return bool
	 */
	public static function setBase(string $base): bool {
		if (self::$base !== $base) {
			$newBaseFolder=\realpath(\ROOT).\DS.$base;
			$oldBaseFolder=realpath(\ROOT.self::$base);
			if (\file_exists($oldBaseFolder) && !\file_exists(realpath($newBaseFolder))) {
				if(\chmod($oldBaseFolder,'0777')) {
					if (\rename($oldBaseFolder, $newBaseFolder)) {
						self::updateClassesNamespace(self::$base, $base);
					}else{
						return false;
					}
				}else{
					return false;
				}
			} else {
				UFileSystem::safeMkdir(\ROOT . $base);
			}
			self::$base = $base;
			$config = Startup::$config;
			$config['mvcNS']['domains'] = $base;
			Startup::updateConfig($config);
			return true;
		}
		return false;
	}
	
	
	/**
	 * Returns the databases with models in the current domain.
	 *
	 * @return array
	 */
	public static function getDatabases(): array {
		$modelsDbs=CacheManager::getModelsDatabases();
		$ns=self::getNamespace('models');
		$result=[];
		foreach ($modelsDbs as $model=>$db){
			if(UString::startswith($model,$ns)){
				$result[$db]=true;
			}
		}
		return \array_keys($result);
	}
}
