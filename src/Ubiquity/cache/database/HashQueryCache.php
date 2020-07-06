<?php

namespace Ubiquity\cache\database;

use Ubiquity\cache\system\ArrayCache;
use Ubiquity\cache\database\traits\MemoryCacheTrait;

/**
 * Ubiquity\cache\database$HashQueryCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class HashQueryCache extends DbCache {
	use MemoryCacheTrait;
	/**
	 *
	 * @var int
	 */
	protected $size;

	protected function hash(string $string) {
		return \substr ( \md5 ( $string ), 0, $this->size );
	}

	public function __construct($cacheSystem = ArrayCache::class, $config = [ ]) {
		parent::__construct ( $cacheSystem, $config );
		$this->size = $config ['size'] ?? 5;
		$this->storeDeferred = $config ['deferred'] ?? false;
	}

	public function store($tableName, $condition, $result) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		// $this->getMemoryCache ( $k );
		$this->memoryCache [$k] [$this->getKey ( $refString )] = $result;
		if ($this->storeDeferred) {
			$this->toStore [] = $k;
		} else {
			$this->cache->store ( $k, 'return ' . $this->asPhpArray ( $this->memoryCache [$k] ) . ';' );
		}
	}

	public function fetch($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		if ($cache = $this->getMemoryCache ( $k )) {
			$key = $this->getKey ( $refString );
			if (isset ( $cache [$key] ))
				return $cache [$key];
		}
		return false;
	}

	public function delete($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		if ($cache = $this->getMemoryCache ( $k )) {
			$key = $this->getKey ( $refString );
			if (isset ( $cache [$key] )) {
				unset ( $cache [$key] );
				$this->cache->store ( $k, 'return ' . $this->asPhpArray ( $cache ) . ';' );
			}
		}
	}
}
