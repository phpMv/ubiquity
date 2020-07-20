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
 * @version 1.0.1
 *
 */
abstract class DbCache {
	protected $cache;
	/**
	 *
	 * @var array
	 */
	protected $memoryCache;
	protected $storeDeferred;
	protected $toStore = [ ];
	public static $active = false;

	protected function getKey($tableName, $condition) {
		return $tableName . \md5 ( $condition );
	}

	public function __construct($cacheSystem = ArrayCache::class, $config = [ ]) {
		if (\is_string ( $cacheSystem )) {
			$this->cache = new $cacheSystem ( CacheManager::getCacheSubDirectory ( 'queries' ), '.query' );
		} else {
			$this->cache = $cacheSystem;
		}
		$this->storeDeferred = $config ['deferred'] ?? false;
	}

	protected function getMemoryCache($key) {
		if (isset ( $this->memoryCache [$key] )) {
			return $this->memoryCache [$key];
		}
		if ($this->cache->exists ( $key )) {
			return $this->memoryCache [$key] = $this->cache->fetch ( $key );
		}
		return false;
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
	public function fetch($tableName, $condition) {
		$key = $this->getKey ( $tableName, $condition );
		if (isset ( $this->memoryCache [$key] )) {
			return $this->memoryCache [$key];
		}
		if ($this->cache->exists ( $key )) {
			return $this->memoryCache [$key] = $this->cache->fetch ( $key );
		}
		return false;
	}

	/**
	 * Deletes the entry corresponding to $condition apply to $table
	 *
	 * @param string $tableName
	 * @param string $condition
	 * @return boolean true if the entry is deleted
	 */
	abstract public function delete($tableName, $condition);

	public function clear($matches = '') {
		$this->cache->clear ( $matches );
	}

	public function remove($key) {
		$this->cache->remove ( $key );
	}

	public function setActive($value = true) {
		self::$active = $value;
	}

	public function storeDeferred() {
		foreach ( $this->toStore as $k ) {
			$this->cache->store ( $k, $this->memoryCache [$k] );
		}
		$this->toStore = [ ];
	}
}
