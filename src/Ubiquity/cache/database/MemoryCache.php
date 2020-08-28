<?php

namespace Ubiquity\cache\database;

/**
 * Ubiquity\cache\database$MemoryCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class MemoryCache extends DbCache {
	/**
	 *
	 * @var array
	 */
	protected $memoryCache;

	public function __construct() {
	}

	public function fetch($tableName, $condition) {
		$key = $this->getKey ( $tableName, $condition );
		if (isset ( $this->memoryCache [$key] )) {
			return $this->memoryCache [$key];
		}
		return false;
	}

	public function store($tableName, $condition, $result) {
		$this->memoryCache [$this->getKey ( $tableName, $condition )] = $result;
	}

	public function delete($tableName, $condition) {
		$key = $this->getKey ( $tableName, $condition );
		if (isset ( $this->memoryCache [$key] )) {
			unset ( $this->memoryCache [$key] );
			return true;
		}
		return false;
	}
}
