<?php

namespace Ubiquity\cache\dao;

/**
 * Ubiquity\cache\dao$DAOMemoryCache
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class DAOMemory5Cache extends AbstractDAOCache {
	/**
	 *
	 * @var array
	 */
	protected $arrayCache;

	protected function getKey($class, $key) {
		return \md5 ( $class . $key );
	}

	public function store($class, $key, $object) {
		$this->arrayCache [$this->getKey ( $class, $key )] = $object;
	}

	public function fetch($class, $key) {
		return $this->arrayCache [$this->getKey ( $class, $key )] ?? false;
	}

	public function delete($class, $key) {
		$k = $this->getKey ( $class, $key );
		if (isset ( $this->arrayCache [$k] )) {
			unset ( $this->arrayCache [$k] );
		}
	}
}

