<?php

namespace Ubiquity\cache\dao;

use Ubiquity\cache\CacheManager;
use Ubiquity\cache\system\AbstractDataCache;
use Ubiquity\cache\system\MemCachedDriver;

/**
 * Ubiquity\cache\dao$DAOCache
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class DAOCache extends AbstractDAOCache {
	/**
	 *
	 * @var array
	 */
	private $items;
	/**
	 *
	 * @var AbstractDataCache
	 */
	protected $cache;

	protected function getKey($class, $key) {
		return \md5 ( $class . $key );
	}

	public function __construct($cacheSystem = MemCachedDriver::class) {
		if (\is_string ( $cacheSystem )) {
			$this->cache = new $cacheSystem ( CacheManager::getCacheSubDirectory ( 'objects' ), '.object' );
		} else {
			$this->cache = $cacheSystem;
		}
	}

	public function store($class, $key, $object) {
		$this->cache->store ( $this->getKey ( $class, $key ), $object );
	}

	public function fetch($class, $key) {
		$k = $this->getKey ( $class, $key );
		if (! isset ( $this->items [$k] )) {
			$this->items [$k] = $this->cache->fetch ( $k );
		}
		return $this->items [$k];
	}

	public function delete($class, $key) {
		$key = $this->getKey ( $class, $key );
		if ($this->cache->exists ( $key )) {
			return $this->cache->remove ( $key );
		}
		return false;
	}
}

