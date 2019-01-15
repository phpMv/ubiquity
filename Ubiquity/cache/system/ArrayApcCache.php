<?php

namespace Ubiquity\cache\system;

/**
 * This class is responsible for storing Arrays in PHP files, and require php APCu.
 */
class ArrayApcCache extends ArrayCache {

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\cache\system\ArrayCache::storeContent()
	 */
	protected function storeContent($key, $content, $tag) {
		parent::storeContent($key, $content, $tag);
		if (apcu_exists($this->_root.$key)){
			apcu_delete($this->_root.$key);
		}
	}
	
	protected function apcDelete($key){
		if (apcu_exists($this->_root.$key)){
			return apcu_delete($this->_root.$key);
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\cache\system\ArrayCache::fetch()
	 */
	public function fetch($key) {
		if (apcu_exists($this->_root.$key)){
			return apcu_fetch($this->_root.$key);
		}
		$content= parent::fetch($key);
		apcu_store($this->_root.$key, $content);
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
