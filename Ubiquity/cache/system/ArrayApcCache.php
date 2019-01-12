<?php

namespace Ubiquity\cache\system;

/**
 * This class is responsible for storing Arrays in PHP files, and require php APC.
 */
class ArrayApcCache extends ArrayCache {

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\cache\system\ArrayCache::storeContent()
	 */
	protected function storeContent($key, $content, $tag) {
		parent::storeContent($key, $content, $tag);
		$apcK=$this->getApcKey($key);
		if (apc_exists($apcK)){
			apc_delete($apcK);
		}
	}
	
	protected function apcDelete($key){
		$apcK=$this->getApcKey($key);
		if (apc_exists($apcK)){
			return apc_delete($apcK);
		}
		return false;
	}
	
	protected function getApcKey($key){
		return md5($this->_root.$key);
	}

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\cache\system\ArrayCache::fetch()
	 */
	public function fetch($key) {
		$apcK=$this->getApcKey($key);
		if (apc_exists($apcK)){
			return apc_fetch($apcK);
		}
		$content= parent::fetch($key);
		apc_store($apcK, $content);
		return $content;
	}


	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::remove()
	 */
	public function remove($key) {
		$this->apcDelete($key);
		return parent::remove($key);
	}


}
