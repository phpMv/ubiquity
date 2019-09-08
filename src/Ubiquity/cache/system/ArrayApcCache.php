<?php

namespace Ubiquity\cache\system;

/**
 * This class is responsible for storing Arrays in PHP files, and require php apc.
 * Ubiquity\cache\system$ArrayApcCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class ArrayApcCache extends ArrayCache {

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\ArrayCache::storeContent()
	 */
	protected function storeContent($key, $content, $tag) {
		parent::storeContent ( $key, $content, $tag );
		$apcK = $this->getApcKey ( $key );
		if ($this->apcExists ( $apcK )) {
			\apc_delete ( $apcK );
		}
	}

	public function apcExists($key) {
		$success = false;
		\apc_fetch ( $key, $success );
		return $success;
	}

	protected function apcDelete($key) {
		$apcK = $this->getApcKey ( $key );
		if ($this->apcExists ( $apcK )) {
			return \apc_delete ( $apcK );
		}
		return false;
	}

	protected function getApcKey($key) {
		return md5 ( $this->_root . $key );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\ArrayCache::fetch()
	 */
	public function fetch($key) {
		$apcK = $this->getApcKey ( $key );
		if ($this->apcExists ( $apcK )) {
			return \apc_fetch ( $apcK );
		}
		$content = parent::fetch ( $key );
		\apc_store ( $apcK, $content );
		return $content;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::remove()
	 */
	public function remove($key) {
		$this->apcDelete ( $key );
		return parent::remove ( $key );
	}
}
