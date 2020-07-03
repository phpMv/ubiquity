<?php

namespace Ubiquity\cache\database;

use Ubiquity\utils\base\UArray;

/**
 * Cache
 * Ubiquity\cache\database$TableCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class TableCache extends DbCache {
	protected $arrayCache;

	public function store($tableName, $condition, $result) {
		$this->getArrayCache ( $tableName );
		$this->arrayCache [$this->getKey ( $condition )] = $result;
		$this->cache->store ( $tableName, "return " . UArray::asPhpArray ( $this->arrayCache, "array" ) . ";" );
	}

	protected function getArrayCache($tableName) {
		if (isset ( $this->arrayCache [$tableName] ))
			return $this->arrayCache [$tableName];
		if ($this->cache->exists ( $tableName )) {
			return $this->arrayCache [$tableName] = $this->cache->fetch ( $tableName );
		}
		return [ ];
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
