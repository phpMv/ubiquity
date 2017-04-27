<?php

namespace micro\cache\database;

use micro\utils\JArray;

class TableCache extends DbCache {
	protected $arrayCache;

	public function store($tableName, $condition, $result) {
		$exists=$this->getCache($tableName);
		$exists[$this->getKey($condition)]=$result;
		$this->cache->store($tableName, "return " . JArray::asPhpArray($exists, "array") . ";");
	}

	public function getCache($tableName) {
		if ($this->cache->exists($tableName))
			return $this->cache->fetch($tableName);
		return [ ];
	}

	protected function getArrayCache($tableName) {
		if (isset($this->arrayCache[$tableName]))
			return $this->arrayCache[$tableName];
		if ($this->cache->exists($tableName)) {
			return $this->arrayCache[$tableName]=$this->cache->fetch($tableName);
		}
		return false;
	}

	public function fetch($tableName, $condition) {
		if ($cache=$this->getArrayCache($tableName)) {
			$key=$this->getKey($condition);
			if (isset($cache[$key]))
				return $cache[$key];
		}
		return false;
	}
}
