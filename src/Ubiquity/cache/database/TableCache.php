<?php

namespace Ubiquity\cache\database;

use Ubiquity\utils\base\UArray;

/**
 * Cache
 * Ubiquity\cache\database$TableCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class TableCache extends DbCache {
	protected $arrayCache;

	public function store($tableName, $condition, $result) {
		$exists = $this->getCache ( $tableName );
		$exists [$this->getKey ( $condition )] = $result;
		$this->cache->store ( $tableName, "return " . UArray::asPhpArray ( $exists, "array" ) . ";" );
	}

	public function getCache($tableName) {
		if ($this->cache->exists ( $tableName ))
			return $this->cache->fetch ( $tableName );
		return [ ];
	}

	protected function getArrayCache($tableName) {
		if (isset ( $this->arrayCache [$tableName] ))
			return $this->arrayCache [$tableName];
		if ($this->cache->exists ( $tableName )) {
			return $this->arrayCache [$tableName] = $this->cache->fetch ( $tableName );
		}
		return false;
	}

	public function fetch($tableName, $condition) {
		if ($cache = $this->getArrayCache ( $tableName )) {
			$key = $this->getKey ( $condition );
			if (isset ( $cache [$key] ))
				return $cache [$key];
		}
		return false;
	}

	public function delete($tableName, $condition) {
		if ($cache = $this->getArrayCache ( $tableName )) {
			$key = $this->getKey ( $condition );
			if (isset ( $cache [$key] )) {
				unset ( $cache [$key] );
				$this->cache->store ( $tableName, "return " . UArray::asPhpArray ( $cache, "array" ) . ";" );
			}
		}
	}
}
