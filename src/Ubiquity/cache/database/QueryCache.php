<?php

namespace Ubiquity\cache\database;

use Ubiquity\cache\database\traits\MemoryCacheTrait;
use Ubiquity\cache\system\ArrayCache;

/**
 * Ubiquity\cache\database$QueryCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class QueryCache extends DbCache {
	use MemoryCacheTrait;

	public function __construct($cacheSystem = ArrayCache::class, $config = [ ]) {
		parent::__construct ( $cacheSystem, $config );
		$this->storeDeferred = $config ['deferred'] ?? true;
	}

	public function fetch($tableName, $condition) {
		return $this->getMemoryCache ( $tableName . '.' . $this->getKey ( $condition ) );
	}

	public function store($tableName, $condition, $result) {
		$key = $tableName . '.' . $this->getKey ( $condition );
		$this->memoryCache [$key] = $result;
		if ($this->storeDeferred) {
			$this->toStore [] = $key;
		} else {
			$this->cache->store ( $key, 'return ' . $this->asPhpArray ( $result ) . ';' );
		}
	}

	public function delete($tableName, $condition) {
		$key = $tableName . '.' . $this->getKey ( $condition );
		if ($this->cache->exists ( $key )) {
			if (isset ( $this->memoryCache [$key] )) {
				unset ( $this->memoryCache [$key] );
			}
			return $this->cache->remove ( $key );
		}
		return false;
	}
}
