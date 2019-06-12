<?php

namespace Ubiquity\translation;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;

/**
 * Store translation updates.
 * Ubiquity\translation$MessagesUpdates
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since Ubiquity 2.1.4
 *
 */
class MessagesUpdates {
	protected $locale;
	protected $domain;
	protected $key = "tmp/translations/";
	protected $toUpdate;
	protected $toAdd;
	protected $toDelete;
	protected $dirty;
	protected $newKeys;

	protected function getKey() {
		return md5 ( $this->locale . '.' . $this->domain );
	}

	public function __construct($locale, $domain) {
		$this->locale = $locale;
		$this->domain = $domain;
		$this->dirty = false;
	}

	public function load() {
		$key = $this->getKey ();
		if (CacheManager::$cache->exists ( $this->key . $key )) {
			$array = CacheManager::$cache->fetch ( $this->key . $key );
		}
		$this->newKeys = $array ['newKeys'] ?? [ ];
		$this->toAdd = $array ['toAdd'] ?? [ ];
		$this->toUpdate = $array ['toUpdate'] ?? [ ];
		$this->toDelete = $array ['toDelete'] ?? [ ];
	}

	public function exists() {
		$key = $this->getKey ();
		return CacheManager::$cache->exists ( $this->key . $key );
	}

	public function hasUpdates() {
		return sizeof ( $this->toUpdate ) > 0 || sizeof ( $this->toDelete ) > 0 || sizeof ( $this->toAdd ) > 0;
	}

	public function mergeMessages($messages, $beforeSave = false) {
		foreach ( $this->toDelete as $k ) {
			if (isset ( $messages [$k] )) {
				unset ( $messages [$k] );
			}
		}
		foreach ( $this->toUpdate as $k => $v ) {
			$messages [$k] = $v;
		}
		foreach ( $this->toAdd as $k => $v ) {
			if (isset ( $this->newKeys [$k] )) {
				if ($beforeSave) {
					$messages [$k] = $v;
				} else {
					$messages [$k] = [ $v,$this->newKeys [$k] ];
				}
			}
		}
		return $messages;
	}

	public function addToDelete($key) {
		$this->toDelete [] = $key;
		if (isset ( $this->toUpdate [$key] )) {
			unset ( $this->toUpdate [$key] );
		}
		$this->dirty = true;
	}

	public function updateValue($key, $value) {
		$this->toUpdate [$key] = $value;
		if (($index = array_search ( $key, $this->toDelete )) !== false) {
			unset ( $this->toDelete [$index] );
		}
		$this->dirty = true;
	}

	public function addValue($key, $value, $keyId = null) {
		$this->toAdd [$key] = $value;
		if (isset ( $keyId )) {
			if (($id = array_search ( $keyId, $this->newKeys )) !== false) {
				unset ( $this->toAdd [$id] );
				unset ( $this->newKeys [$id] );
			}
			$this->newKeys [$key] = $keyId;
		}

		$this->dirty = true;
	}

	public function removeAddValue($key) {
		if (isset ( $this->toAdd [$key] )) {
			unset ( $this->toAdd [$key] );
		}
	}

	public function replaceKey($key, $newKey, $value) {
		$this->addToDelete ( $key );
		$this->updateValue ( $newKey, $value );
	}

	public function removeNewKey($key) {
		if (($k = array_search ( $key, $this->newKeys )) !== false) {
			if (isset ( $this->toAdd [$k] )) {
				unset ( $this->toAdd [$k] );
			}
			unset ( $this->newKeys [$k] );
			$this->dirty = true;
		}
	}

	public function save() {
		if ($this->dirty) {
			$key = $this->getKey ();
			CacheManager::$cache->store ( $this->key . $key, 'return array' . UArray::asPhpArray ( [ 'newKeys' => $this->newKeys,'toAdd' => $this->toAdd,'toUpdate' => $this->toUpdate,'toDelete' => $this->toDelete ] ) . ';' );
			$this->dirty = false;
			return true;
		}
		return false;
	}

	public function delete() {
		$key = $this->getKey ();
		CacheManager::$cache->remove ( $this->key . $key );
	}

	public function isDirty() {
		return $this->dirty;
	}

	public function __toString() {
		$res = [ ];
		if (($nb = sizeof ( $this->toAdd )) > 0) {
			$res [] = '+' . $nb;
		}
		if (($nb = sizeof ( $this->toUpdate )) > 0) {
			$res [] = '±' . $nb;
		}
		if (($nb = sizeof ( $this->toDelete )) > 0) {
			$res [] = '-' . $nb;
		}
		return implode ( ', ', $res );
	}
}

