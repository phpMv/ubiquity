<?php

namespace Ubiquity\cache\system;

use Ubiquity\utils\base\UString;
use Ubiquity\cache\CacheFile;

/**
 * APC cache implementation
 * Ubiquity\cache\system$ApcCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class ApcuCache extends AbstractDataCache {

	/**
	 * Initializes the apc cache-provider
	 */
	public function __construct($root, $postfix = "") {
		parent::__construct ( $root, $postfix );
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 *
	 * @param string $key cache key
	 * @return string[]|boolean true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		$success = false;
		\apc_fetch ( $this->getRealKey ( $key ), $success );
		return $success;
	}

	public function store($key, $code, $tag = null, $php = true) {
		$this->storeContent ( $key, $code, $tag );
	}

	/**
	 * Caches the given data with the given key.
	 *
	 * @param string $key cache key
	 * @param string $content the source-code to be cached
	 * @param string $tag not used
	 */
	protected function storeContent($key, $content, $tag) {
		\apc_store ( $this->getRealKey ( $key ), $content );
	}

	protected function getRealKey($key) {
		return $key;
	}

	/**
	 * Fetches data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function fetch($key) {
		$result = \apc_fetch ( $this->getRealKey ( $key ) );
		return eval ( $result );
	}

	/**
	 * return data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function file_get_contents($key) {
		return \apc_fetch ( $this->getRealKey ( $key ) );
	}

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key cache key
	 * @return boolean|int unix timestamp
	 */
	public function getTimestamp($key) {
		$key = $this->getRealKey ( $key );
		$cache = \apc_cache_info ( 'user' );
		if (empty ( $cache ['cache_list'] )) {
			return false;
		}
		foreach ( $cache ['cache_list'] as $entry ) {
			if ($entry ['info'] != $key) {
				continue;
			}
			$creationTime = $entry ['creation_time'];
			return $creationTime;
		}
		return \time ();
	}

	public function remove($key) {
		\apc_delete ( $this->getRealKey ( $key ) );
	}

	public function clear() {
		\apc_clear_cache ( 'user' );
	}

	protected function getCacheEntries($type) {
		$entries = $this->getAllEntries ();
		return \array_filter ( $entries, function ($v) use ($type) {
			return UString::startswith ( $v ['info'], $type );
		} );
	}

	protected function getAllEntries() {
		$entries = [ ];
		$cache = \apc_cache_info ( 'user' );
		if (! empty ( $cache ['cache_list'] )) {
			$entries = $cache ['cache_list'];
		}
		return $entries;
	}

	public function getCacheFiles($type) {
		$result = [ ];
		$entries = $this->getCacheEntries ( $type );
		foreach ( $entries as $entry ) {
			$key = $entry ['info'];
			if (UString::startswith ( $key, $type )) {
				$result [] = new CacheFile ( \ucfirst ( $type ), $key, $entry ['creation_time'], $entry ['mem_size'], $key );
			}
		}
		if (\sizeof ( $result ) === 0)
			$result [] = new CacheFile ( \ucfirst ( $type ), "", "", "" );
		return $result;
	}

	public function clearCache($type) {
		$entries = $this->getCacheEntries ( $type );
		foreach ( $entries as $entry ) {
			$this->remove ( $entry ['info'] );
		}
	}

	public function getEntryKey($key) {
		return $this->getRealKey ( $key );
	}
}
