<?php

namespace Ubiquity\cache\database;

/**
 * Ubiquity\cache\database$QueryCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class QueryCache extends DbCache {

	public function store($tableName, $condition, $result) {
		$key = $this->getKey ( $tableName, $condition );
		$this->memoryCache [$key] = $result;
		if ($this->storeDeferred) {
			$this->toStore [] = $key;
		} else {
			$this->cache->store ( $key, $result );
		}
	}

	public function delete($tableName, $condition) {
		$key = $this->getKey ( $tableName, $condition );
		if ($this->cache->exists ( $key )) {
			if (isset ( $this->memoryCache [$key] )) {
				unset ( $this->memoryCache [$key] );
			}
			return $this->cache->remove ( $key );
		}
		return false;
	}
}
