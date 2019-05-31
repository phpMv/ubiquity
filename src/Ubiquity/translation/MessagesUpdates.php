<?php

namespace Ubiquity\translation;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;

/**
 * Ubiquity\translation$MessagesUpdates
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class MessagesUpdates {
	protected $locale;
	protected $domain;
	protected $key = "tmp/translations/";
	protected $values;
	protected $toDelete;
	protected $dirty;

	protected function getKey() {
		return md5 ( $this->locale . '.' . $this->domain );
	}

	public function __construct($locale, $domain) {
		$this->locale = $locale;
		$this->domain = $domain;
		$this->dirty = false;
	}

	public function load() {
		$key = self::getKey ();
		if (CacheManager::$cache->exists ( $this->key . $key )) {
			$array = CacheManager::$cache->fetch ( $this->key . $key );
		}
		$this->values = $array ['values'] ?? [ ];
		$this->toDelete = $array ['toDelete'] ?? [ ];
	}

	public function exists() {
		$key = self::getKey ();
		return CacheManager::$cache->exists ( $this->key . $key );
	}

	public function hasUpdates() {
		return sizeof ( $this->values ) > 0 || sizeof ( $this->toDelete ) > 0;
	}

	public function mergeMessages($messages) {
		foreach ( $this->toDelete as $k ) {
			if (isset ( $messages [$k] )) {
				unset ( $messages [$k] );
			}
		}
		foreach ( $this->values as $k => $v ) {
			$messages [$k] = $v;
		}
		return $messages;
	}

	public function addToDelete($key) {
		$this->toDelete [] = $key;
		if (isset ( $this->values [$key] )) {
			unset ( $this->values [$key] );
		}
		$this->dirty = true;
	}

	public function addValue($key, $value) {
		$this->values [$key] = $value;
		if (($index = array_search ( $key, $this->toDelete )) !== false) {
			unset ( $this->toDelete [$index] );
		}
		$this->dirty = true;
	}

	public function replaceKey($key, $newKey, $value) {
		$this->addToDelete ( $key );
		$this->addValue ( $newKey, $value );
	}

	public function removeNewKey($key) {
		unset ( $this->values [$key] );
		$this->dirty = true;
	}

	public function save() {
		if ($this->dirty) {
			$key = self::getKey ();
			CacheManager::$cache->store ( $this->key . $key, 'return array' . UArray::asPhpArray ( [ 'values' => $this->values,'toDelete' => $this->toDelete ] ) . ';' );
			$this->dirty = false;
			return true;
		}
		return false;
	}

	public function delete() {
		$key = self::getKey ();
		CacheManager::$cache->remove ( $this->key . $key );
	}

	public function isDirty() {
		return $this->dirty;
	}

	public function __toString() {
		$res = [ ];
		if (($nb = sizeof ( $this->values )) > 0) {
			$res [] = $nb . ' updates ';
		}
		if (($nb = sizeof ( $this->toDelete )) > 0) {
			$res [] = $nb . ' deletions ';
		}
		return implode ( ', ', $res );
	}
}

