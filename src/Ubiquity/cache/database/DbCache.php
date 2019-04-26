<?php

/**
 * Database cache systems
 */
namespace Ubiquity\cache\database;

use Ubiquity\cache\system\ArrayCache;
use Ubiquity\cache\CacheManager;

/**
 * Abstract class for database caching
 * Ubiquity\cache\database$DbCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class DbCache {
	protected $cache;
	protected $config;
	public static $active = false;

	protected function getKey($query) {
		return \md5 ( $query );
	}

	public function __construct() {
		$this->cache = new ArrayCache ( CacheManager::getCacheSubDirectory ( "queries" ), ".query" );
	}

	/**
	 * Caches the given data with the given key (tableName+md5(condition)).
	 *
	 * @param string $tableName
	 * @param string $condition
	 * @param array $result the datas to store
	 */
	abstract public function store($tableName, $condition, $result);

	/**
	 * Fetches data stored for the given condition in table.
	 *
	 * @param string $tableName
	 * @param string $condition
	 * @return mixed the cached datas
	 */
	abstract public function fetch($tableName, $condition);

	/**
	 * Deletes the entry corresponding to $condition apply to $table
	 *
	 * @param string $tableName
	 * @param string $condition
	 * @return boolean true if the entry is deleted
	 */
	abstract public function delete($tableName, $condition);

	public function clear($matches = "") {
		$this->cache->clear ( $matches );
	}

	public function remove($key) {
		$this->cache->remove ( $key );
	}

	public function setActive($value = true) {
		self::$active = $value;
	}
}
