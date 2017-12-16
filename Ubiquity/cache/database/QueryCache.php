<?php

namespace Ubiquity\cache\database;

use Ubiquity\cache\database\DbCache;
use Ubiquity\utils\JArray;

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
