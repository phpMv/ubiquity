<?php

namespace micro\cache\database;

use micro\cache\ArrayCache;
use micro\cache\CacheManager;

abstract class DbCache {
	protected $cache;
	protected $config;
	public static $active=false;

	protected function getKey($query) {
		return \md5($query);
	}

	public function __construct() {
		$cacheDirectory=ROOT . DS . CacheManager::getCacheDirectory() . DS . "queries";
		$this->cache=new ArrayCache($cacheDirectory, ".query");
		self::$active=true;
	}

	abstract public function store($tableName, $condition, $result);

	abstract public function fetch($tableName, $condition);

	public function clear() {
		$this->cache->clear();
	}

	public function remove($element) {
		$this->cache->remove($element);
	}

	public function setActive($value=true) {
		self::$active=$value;
	}
}
