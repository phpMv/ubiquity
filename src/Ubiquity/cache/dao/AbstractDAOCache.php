<?php

namespace Ubiquity\cache\dao;

/**
 * Ubiquity\cache\dao$AbstractDAOCache
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
abstract class AbstractDAOCache {

	abstract public function store($class, $key, $object);

	abstract public function fetch($class, $key);

	abstract public function delete($class, $key);

	public function optimize() {
	}
}

