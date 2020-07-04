<?php
namespace Ubiquity\cache\database;

use Ubiquity\utils\base\UArray;

/**
 * Ubiquity\cache\database$HashQueryCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class HashQueryCache extends DbCache {

	private $size = 10;

	private function hash($string) {
		$sum = 0;
		$size = \strlen($string);
		for ($i = 0; $i < $size; $i ++) {
			$sum += \ord($string[$i]);
		}
		return $sum % $this->size;
	}

	public function __construct($size = 100) {
		parent::__construct();
		$this->size = $size;
	}

	public function store($tableName, $condition, $result) {
		$refString = $tableName . $condition;
		$k = $this->hash($refString);
		$this->getArrayCache($k);
		$this->arrayCache[$this->getKey($refString)] = $result;
		$this->cache->store($k, "return " . UArray::asPhpArray($this->arrayCache, "array") . ";");
	}

	protected function getArrayCache($key) {
		if (isset($this->arrayCache[$key]))
			return $this->arrayCache[$key];
		if ($this->cache->exists($key)) {
			return $this->arrayCache[$key] = $this->cache->fetch($key);
		}
		return [];
	}

	public function fetch($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash($refString);
		if ($cache = $this->getArrayCache($k)) {
			$key = $this->getKey($refString);
			if (isset($cache[$key]))
				return $cache[$key];
		}
		return false;
	}

	public function delete($tableName, $condition) {
		$refString = $tableName . $condition;
		$k = $this->hash($refString);
		if ($cache = $this->getArrayCache($k)) {
			$key = $this->getKey($refString);
			if (isset($cache[$key])) {
				unset($cache[$key]);
				$this->cache->store($k, "return " . UArray::asPhpArray($cache, "array") . ";");
			}
		}
	}
}
