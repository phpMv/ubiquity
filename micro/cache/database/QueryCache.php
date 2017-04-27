<?php

namespace micro\cache\database;

use micro\cache\database\DbCache;
use micro\utils\JArray;

class QueryCache extends DbCache {

	public function fetch($tableName, $condition) {
		$key=$tableName . "." . $this->getKey($condition);
		if ($this->cache->exists($key))
			return $this->cache->fetch($key);
		return false;
	}

	public function store($tableName, $condition, $result) {
		$this->cache->store($tableName . "." . $this->getKey($condition), "return " . JArray::asPhpArray($result, "array") . ";");
	}
}
