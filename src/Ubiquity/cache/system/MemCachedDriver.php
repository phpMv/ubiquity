<?php

namespace Ubiquity\cache\system;

use Ubiquity\cache\CacheFile;

/**
 * This class is responsible for storing values with MemCached.
 * Ubiquity\cache\system$MemCachedDriver
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class MemCachedDriver extends AbstractDataCache {
	/**
	 *
	 * @var \Memcached
	 */
	private $cacheInstance;
	private const CONTENT = 'content';
	private const TAG = 'tag';
	private const TIME = 'time';

	/**
	 * Initializes the cache-provider
	 */
	public function __construct($root, $postfix = "", $cacheParams = []) {
		parent::__construct ( $root, $postfix );
		$defaultParams = [ 'server' => '127.0.0.1','port' => 11211 ];
		$cacheParams = \array_merge ( $cacheParams, $defaultParams );
		$this->cacheInstance = new \Memcached ();
		$this->cacheInstance->addServer ( $cacheParams ['server'], $cacheParams ['port'] );
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 *
	 * @param string $key cache key
	 * @return boolean true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		$k = $this->getRealKey ( $key );
		$this->cacheInstance->get ( $k );
		return \Memcached::RES_NOTFOUND !== $this->cacheInstance->getResultCode ();
	}

	public function store($key, $code, $tag = null, $php = true) {
		$this->storeContent ( $key, $code, $tag );
	}

	/**
	 * Caches the given data with the given key.
	 *
	 * @param string $key cache key
	 * @param string $content the source-code to be cached
	 * @param string $tag
	 */
	protected function storeContent($key, $content, $tag) {
		$key = $this->getRealKey ( $key );
		$this->cacheInstance->set ( $key, [ self::CONTENT => $content,self::TAG => $tag,self::TIME => \time () ] );
	}

	protected function getRealKey($key) {
		$key = \str_replace ( "/", "-", $key );
		return \str_replace ( "\\", "-", $key );
	}

	/**
	 * Fetches data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function fetch($key) {
		$result = $this->cacheInstance->get ( $this->getRealKey ( $key ) ) [self::CONTENT];
		return eval ( $result );
	}

	/**
	 * return data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function file_get_contents($key) {
		return $this->cacheInstance->get ( $this->getRealKey ( $key ) ) [self::CONTENT];
	}

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key cache key
	 * @return int unix timestamp
	 */
	public function getTimestamp($key) {
		$key = $this->getRealKey ( $key );
		return $this->cacheInstance->get ( $key ) [self::TIME];
	}

	public function remove($key) {
		$key = $this->getRealKey ( $key );
		$this->cacheInstance->delete ( $this->getRealKey ( $key ) );
	}

	public function clear() {
		$this->cacheInstance->flush ();
	}

	public function getCacheFiles($type) {
		$result = [ ];
		$keys = $this->cacheInstance->getAllKeys ();

		foreach ( $keys as $key ) {
			$entry = $this->cacheInstance->get ( $key );
			if ($entry [self::TAG] === $type) {
				$result [] = new CacheFile ( \ucfirst ( $type ), $key, $entry [self::TIME], "", $key );
			}
		}
		if (\sizeof ( $result ) === 0)
			$result [] = new CacheFile ( \ucfirst ( $type ), "", "", "" );
		return $result;
	}

	public function clearCache($type) {
		$keys = $this->cacheInstance->getAllKeys ();
		foreach ( $keys as $key ) {
			$entry = $this->cacheInstance->get ( $key );
			if ($entry [self::TAG] === $type) {
				$this->cacheInstance->delete ( $key );
			}
		}
	}

	public function getCacheInfo() {
		return parent::getCacheInfo () . "<br>Driver name : <b>" . \Memcached::class . "</b>";
	}

	public function getEntryKey($key) {
		return $this->getRealKey ( $key );
	}
}
