<?php

namespace Ubiquity\cache\database;

/**
 * Ubiquity\cache\database$MemoryCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class HashMemoryCache extends DbCache {

	/**
	 *
	 * @var int
	 */
	protected $size;

	/**
	 *
	 * @var array
	 */
	protected $memoryCache;

	protected function hash(string $string) {
		return \substr ( \md5 ( $string ), 0, $this->size );
	}

	public function __construct($config = [ ]) {
		$this->size = $config ['size'] ?? 5;
	}

	public function fetch($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		return ($this->memoryCache [$k] [$this->getKey ( $refString )]) ?? false;
	}

	public function store($tableName, $condition, $result) {
		$refString = $tableName . $condition;
		$this->memoryCache [$this->hash ( $refString )] [$this->getKey ( $refString )] = $result;
	}

	public function delete($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash ( $refString );
		if (isset ( $this->memoryCache [$k] )) {
			$key = $this->getKey ( $refString );
			if (isset ( $this->memoryCache [$k] [$key] )) {
				unset ( $this->memoryCache [$k] [$key] );
				return true;
			}
		}
		return false;
	}
}
