<?php

namespace micro\cache;
use micro\utils\JArray;

class QueryCache{
	protected static $cache;
	protected static $config;
	public static $active;

	protected static function getKey($query){
		return "query".\md5($query);
	}

	public static function start(&$config){
		self::$config=$config;
		$cacheDirectory=ROOT.DS.CacheManager::getCacheDirectory($config).DS."queries";
		self::$cache=new ArrayCache($cacheDirectory,".query");
		self::$active=true;
	}

	public static function store($query,$result){
		self::$cache->store(self::getKey($query),"return ".JArray::asPhpArray($result,"array").";");
	}

	public static function fetch($query){
		$key=self::getKey($query);
		if(self::$cache->exists($key))
			return self::$cache->fetch($key);
		return false;
	}

	public function clear(){
		self::$cache->clear();
	}

	public function remove($query){
		self::$cache->remove($query);
	}

	public static function setActive($value=true){
		self::$active=$value;
	}
}
