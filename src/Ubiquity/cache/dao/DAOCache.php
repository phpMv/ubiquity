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
		if (\method_exists ( $this->cache, 'setUseArrays' )) {
			$this->cache->setUseArrays ( false );
		}
	}

	public function store($class, $key, $object) {
		$this->cache->store ( $this->getKey ( $class, $key ), $object );
	}

	public function fetch($class, $key) {
		return $this->cache->fetch ( $this->getKey ( $class, $key ) );
	}

	public function delete($class, $key) {
		$key = $this->getKey ( $class, $key );
		if ($this->cache->exists ( $key )) {
			return $this->cache->remove ( $key );
		}
		return false;
	}
}

