<?php

namespace Ubiquity\cache\system;

use Ubiquity\cache\CacheFile;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;

/**
 * This class is responsible for storing values with PhpFastCache.
 * Ubiquity\cache\system$PhpFastCacheDriver
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.0
 *
 */
class PhpFastCacheDriver extends AbstractDataCache {
	/**
	 *
	 * @var ExtendedCacheItemPoolInterface
	 */
	private $cacheInstance;

	/**
	 * Initializes the cache-provider
	 */
	public function __construct($root, $postfix = "", $cacheParams = []) {
		parent::__construct ( $root, $postfix );
		$cacheType = $cacheParams ['type']??'Files';
		unset ( $cacheParams ['type'] );
		$defaultParams = [ 'defaultTtl' => 86400,'itemDetailedDate' => true ];
		$cacheParams = \array_merge ( $cacheParams, $defaultParams );
		$this->cacheInstance = CacheManager::getInstance ( $cacheType, new Config ( $cacheParams ) );
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 *
	 * @param string $key cache key
	 * @return boolean true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		return $this->cacheInstance->hasItem ( $this->getRealKey ( $key ) );
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
		$item = $this->cacheInstance->getItem ( $key );
		$item->set ( $content );
		$item->addTag ( $tag );
		$this->cacheInstance->save ( $item );
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
		$result = $this->cacheInstance->getItem ( $this->getRealKey ( $key ) )->get ();
		return eval ( $result );
	}

	/**
	 * return data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function file_get_contents($key) {
		return $this->cacheInstance->getItem ( $this->getRealKey ( $key ) )->get ();
	}

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key cache key
	 * @return int unix timestamp
	 */
	public function getTimestamp($key) {
		$key = $this->getRealKey ( $key );
		return $this->cacheInstance->getItem ( $key )->getModificationDate ()->getTimestamp ();
	}

	public function remove($key) {
		$key = $this->getRealKey ( $key );
		$this->cacheInstance->deleteItem ( $this->getRealKey ( $key ) );
	}

	public function clear() {
		$this->cacheInstance->clear ();
	}

	protected function getCacheEntries($type) {
		return $this->cacheInstance->getItemsByTag ( $type );
	}

	public function getCacheFiles($type) {
		$result = [ ];
		$entries = $this->getCacheEntries ( $type );

		foreach ( $entries as $entry ) {
			$key = $entry->getKey ();
			$result [] = new CacheFile ( \ucfirst ( $type ), $key, $entry->getCreationDate ()->getTimestamp (), "", $key );
		}
		if (\sizeof ( $result ) === 0)
			$result [] = new CacheFile ( \ucfirst ( $type ), "", "", "" );
		return $result;
	}

	public function clearCache($type) {
		$this->cacheInstance->deleteItemsByTag ( $type );
	}

	public function getCacheInfo() {
		return parent::getCacheInfo () . "<br>Driver name : <b>" . $this->cacheInstance->getDriverName () . "</b>";
	}

	public function getEntryKey($key) {
		return $this->cacheInstance->getItem ( $this->getRealKey ( $key ) )->getKey ();
	}
}
