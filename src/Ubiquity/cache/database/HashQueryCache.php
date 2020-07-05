<?php

namespace Ubiquity\cache\database;

use Ubiquity\cache\system\ArrayCache;

/**
 * Ubiquity\cache\database$HashQueryCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class HashQueryCache extends DbCache {
	/**
	 *
	 * @var int
	 */
	protected $size;
	/**
	 *
	 * @var array
	 */
	protected $arrayCache;
	protected $storeDeferred;
	protected $toStore = [ ];

	protected function hash(string $string) {
		return \substr ( \md5 ( $string ), 0, $this->size );
	}

	public function __construct($cacheSystem = ArrayCache::class, $config = [ ]) {
		parent::__construct ( $cacheSystem, $config );
		$this->size = $config ['size'] ?? 5;
		$this->storeDeferred = $config ['deferred'] ?? true;
	}

	public function store($tableName, $condition, $result) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		$this->getArrayCache ( $k );
		$this->arrayCache [$k] [$this->getKey ( $refString )] = $result;
		if ($this->storeDeferred) {
			$this->toStore [] = $k;
		} else {
			$this->cache->store ( $k, 'return ' . $this->asPhpArray ( $this->arrayCache [$k] ) . ';' );
		}
	}

	public function storeDeferred() {
		foreach ( $this->toStore as $k ) {
			$this->cache->store ( $k, 'return ' . $this->asPhpArray ( $this->arrayCache [$k] ) . ';' );
		}
		$this->toStore = [ ];
	}

	protected function getArrayCache($key) {
		if (isset ( $this->arrayCache [$key] )) {
			return $this->arrayCache [$key];
		}
		if ($this->cache->exists ( $key )) {
			return $this->arrayCache [$key] = $this->cache->fetch ( $key );
		}
		return false;
	}

	public function fetch($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		if ($cache = $this->getArrayCache ( $k )) {
			$key = $this->getKey ( $refString );
			if (isset ( $cache [$key] ))
				return $cache [$key];
		}
		return false;
	}

	public function delete($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		if ($cache = $this->getArrayCache ( $k )) {
			$key = $this->getKey ( $refString );
			if (isset ( $cache [$key] )) {
				unset ( $cache [$key] );
				$this->cache->store ( $k, 'return ' . $this->asPhpArray ( $cache ) . ';' );
			}
		}
	}
}
