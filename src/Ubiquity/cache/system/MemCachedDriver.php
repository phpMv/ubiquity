<?php

namespace Ubiquity\cache\system;

use Ubiquity\cache\CacheFile;
use Ubiquity\exceptions\CacheException;

/**
 * This class is responsible for storing values with MemCached.
 * Ubiquity\cache\system$MemCachedDriver
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
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
	public function __construct($root, $postfix = "", $cacheParams = [ ]) {
		parent::__construct ( $root, $postfix );
		$defaultParams = [ 'servers' => [ [ 'host' => '0.0.0.0','port' => 11211 ] ],'serializer' => \Memcached::SERIALIZER_PHP,'persistent' => true ];
		$cacheParams = \array_merge ( $defaultParams, $cacheParams );
		$this->cacheInstance = new \Memcached ( $cacheParams ['persistent'] ? \crc32 ( $root ) : null );
		if (isset ( $cacheParams ['serializer'] )) {
			$this->cacheInstance->setOption ( \Memcached::OPT_SERIALIZER, $cacheParams ['serializer'] );
		}
		if ($this->cacheInstance->isPristine ()) {
			$this->addServers ( $cacheParams ['servers'] );
		}
	}

	public function addServer($host, $port, $weight = null) {
		$this->cacheInstance->addServer ( $host, $port, $weight );
		$statuses = $this->cacheInstance->getStats ();
		if (! isset ( $statuses ["$host:$port"] )) {
			throw new CacheException ( "Connection to the server $host:$port failed!" );
		}
	}

	public function addServers(array $servers) {
		foreach ( $servers as $srv ) {
			$this->addServer ( $srv ['host'] ?? '0.0.0.0', $srv ['port'] ?? 11211, $srv ['weight'] ?? null);
		}
	}

	public function setSerializer($serializer) {
		$this->cacheInstance->setOption ( \Memcached::OPT_SERIALIZER, $serializer );
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

	public function store($key, $content, $tag = null) {
		$this->cacheInstance->set ( $this->getRealKey ( $key ), [ self::CONTENT => $content,self::TAG => $tag,self::TIME => \time () ] );
	}

	protected function getRealKey($key) {
		return \crc32 ( $key );
	}

	/**
	 * Fetches data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function fetch($key) {
		$entry = $this->cacheInstance->get ( $this->getRealKey ( $key ) );
		return $entry [self::CONTENT] ?? false;
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
		if (\count ( $result ) === 0)
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