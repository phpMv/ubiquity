<?php

namespace Ubiquity\cache\dao;

use Ubiquity\cache\CacheManager;
use Ubiquity\cache\system\ArrayCache;
use Ubiquity\contents\serializers\SerializerInterface;
use Ubiquity\contents\serializers\PhpSerializer;
use Ubiquity\cache\system\AbstractDataCache;

/**
 * Ubiquity\cache\dao$DAOSerialCache
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class DAOSerialCache extends AbstractDAOCache {

	/**
	 *
	 * @var SerializerInterface
	 */
	protected $serializer;

	/**
	 *
	 * @var AbstractDataCache
	 */
	protected $cache;

	protected function getKey($value) {
		return \md5 ( $value );
	}

	public function __construct($cacheSystem = ArrayCache::class, $serializer = PhpSerializer::class) {
		if (\is_string ( $cacheSystem )) {
			$this->cache = new $cacheSystem ( CacheManager::getCacheSubDirectory ( 'objects' ), '.object' );
		} else {
			$this->cache = $cacheSystem;
		}
		$this->serializer = new $serializer ();
	}

	public function store($class, $key, $object) {
		$this->cache->store ( $this->getKey ( $class . $key ), $this->serializer->serialize ( $object ) );
	}

	public function fetch($class, $key) {
		$result = $this->cache->fetch ( $this->getKey ( $class . $key ) );
		if ($result) {
			return $this->serializer->unserialize ( $result );
		}
		return false;
	}

	public function delete($class, $key) {
		$key = $this->getKey ( $class . $key );
		if ($this->cache->exists ( $key )) {
			return $this->cache->remove ( $key );
		}
		return false;
	}
}

