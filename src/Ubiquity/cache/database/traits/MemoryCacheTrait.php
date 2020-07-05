<?php

namespace Ubiquity\cache\database\traits;

/**
 * Ubiquity\cache\database$MemoryCacheTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 * @property \Ubiquity\cache\system\AbstractDataCache $cache
 *
 */
trait MemoryCacheTrait {
	/**
	 *
	 * @var array
	 */
	protected $memoryCache;
	protected $storeDeferred;
	protected $toStore = [ ];

	protected function getMemoryCache($key) {
		if (isset ( $this->memoryCache [$key] )) {
			return $this->memoryCache [$key];
		}
		if ($this->cache->exists ( $key )) {
			return $this->memoryCache [$key] = $this->cache->fetch ( $key );
		}
		return false;
	}

	public function storeDeferred() {
		foreach ( $this->toStore as $k ) {
			$this->cache->store ( $k, 'return ' . $this->asPhpArray ( $this->memoryCache [$k] ) . ';' );
		}
		$this->toStore = [ ];
	}
}

