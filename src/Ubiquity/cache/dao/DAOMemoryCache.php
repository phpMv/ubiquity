<?php

namespace Ubiquity\cache\dao;

/**
 * Simple Memory cache for DAO instances
 * Ubiquity\cache\dao$DAOMemoryCache
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class DAOMemoryCache extends AbstractDAOCache {
	/**
	 *
	 * @var array
	 */
	protected $arrayCache;

	public function store($class, $key, $object) {
		$this->arrayCache [$class] [$key] = $object;
	}

	public function fetch($class, $key) {
		return $this->arrayCache [$class] [$key] ?? false;
	}

	public function delete($class, $key) {
		if (isset ( $this->arrayCache [$class] [$key] )) {
			unset ( $this->arrayCache [$class] [$key] );
		}
	}
}

