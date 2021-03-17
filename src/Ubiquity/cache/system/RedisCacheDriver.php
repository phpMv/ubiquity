<?php

namespace Ubiquity\cache\system;

use Ubiquity\cache\CacheFile;

/**
 * This class is responsible for storing values with Redis.
 * Ubiquity\cache\system$RedisCacheDriver
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class RedisCacheDriver extends AbstractDataCache {
	/**
	 *
	 * @var \Redis
	 */
	private $cacheInstance;

	/**
	 * Initializes the cache-provider
	 */
	public function __construct($root, $postfix = "", $cacheParams = [ ]) {
		parent::__construct ( $root, $postfix );
		$defaultParams = [ 'server' => '0.0.0.0','port' => 6379,'persistent' => true,'serializer' => \Redis::SERIALIZER_PHP ];
		$cacheParams = \array_merge ( $defaultParams, $cacheParams );
		$this->cacheInstance = new \Redis ();
		$connect = 'connect';
		if ($cacheParams ['persistent'] ?? true) {
			$connect = 'pconnect';
		}
		$this->cacheInstance->{$connect} ( $cacheParams ['server'], $cacheParams ['port'] );
		if (isset ( $cacheParams ['serializer'] )) {
			$this->cacheInstance->setOption ( \Redis::OPT_SERIALIZER, $cacheParams ['serializer'] );
		}
	}

	public function setSerializer($serializer) {
		$this->cacheInstance->setOption ( \Redis::OPT_SERIALIZER, $serializer );
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 *
	 * @param string $key cache key
	 * @return boolean true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		return $this->cacheInstance->exists ( $this->getRealKey ( $key ) );
	}

	public function store($key, $content, $tag = null) {
		$this->cacheInstance->set ( $this->getRealKey ( $key ), $content );
	}

	protected function getRealKey($key) {
		return \str_replace ( [ '/','\\' ], "-", $key );
	}

	/**
	 * Fetches data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function fetch($key) {
		return $this->cacheInstance->get ( $this->getRealKey ( $key ) );
	}

	/**
	 * return data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function file_get_contents($key) {
		return $this->cacheInstance->get ( $this->getRealKey ( $key ) );
	}

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key cache key
	 * @return int unix timestamp
	 */
	public function getTimestamp($key) {
		return $this->cacheInstance->ttl ( $this->getRealKey ( $key ) );
	}

	public function remove($key) {
		$this->cacheInstance->delete ( $this->getRealKey ( $key ) );
	}

	public function clear() {
		$this->cacheInstance->flushAll ();
	}

	public function getCacheFiles($type) {
		$result = [ ];
		$keys = $this->cacheInstance->keys ( $type );

		foreach ( $keys as $key ) {
			$ttl = $this->cacheInstance->ttl ( $key );
			$result [] = new CacheFile ( \ucfirst ( $type ), $key, $ttl, "", $key );
		}
		if (\count ( $result ) === 0)
			$result [] = new CacheFile ( \ucfirst ( $type ), "", "", "" );
		return $result;
	}

	public function clearCache($type) {
		$keys = $this->cacheInstance->keys ( $type );
		foreach ( $keys as $key ) {
			$this->cacheInstance->delete ( $key );
		}
	}

	public function getCacheInfo() {
		return parent::getCacheInfo () . "<br>Driver name : <b>" . \Redis::class . "</b>";
	}

	public function getEntryKey($key) {
		return $this->getRealKey ( $key );
	}
}
