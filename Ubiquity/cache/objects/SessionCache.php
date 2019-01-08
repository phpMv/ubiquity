<?php

namespace Ubiquity\cache\objects;

use Ubiquity\utils\http\USession;
use Ubiquity\utils\base\UString;

class SessionCache {
	private $session;
	const ENTRY_KEY="_session_cache";
	
	public function __construct(){
		$this->session=new USession();
		$this->session->start();
	}

	public function fetch($key) {
		return $this->session->get($this->getEntryKey($key));
	}

	public function clear() {
		foreach($_SESSION as $k=>$notUsed){
			if(UString::startswith($k, self::ENTRY_KEY.".")){
				unset ( $_SESSION [$k] );
			}
		}
	}

	public function exists($key) {
		return $this->session->exists($this->getEntryKey($key));
	}

	public function getEntryKey($key) {
		return self::ENTRY_KEY.'.'.$key;
	}
	
	public function store($key, $object) {
		$key=$this->getEntryKey($key);
		$this->session->set($key, $object);
	}


	public function remove($key) {
		$this->session->delete($this->getEntryKey($key));
	}
}

