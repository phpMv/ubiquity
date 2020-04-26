<?php

namespace Ubiquity\utils\http\session;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\http\UCookie;

/**
 * Multi-sites session.
 * Ubiquity\utils\http\session$MultisiteSession
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1-beta
 *
 */
class MultisiteSession extends AbstractSession {
	private $folder;
	private $id;
	const SESSION_ID = 'multi_session_id';

	private function getKey($key) {
		return \md5 ( $key );
	}

	private function getFilename($key) {
		return $this->folder . $this->id . \DS . $this->getKey ( $key ) . '.cache.ser';
	}

	protected function generateId() {
		return \bin2hex ( \random_bytes ( 32 ) );
	}

	public function set($key, $value) {
		$val = \serialize ( $value );
		$tmp = "/tmp/$key." . \uniqid ( '', true ) . '.tmp';
		\file_put_contents ( $tmp, $val, LOCK_EX );
		\rename ( $tmp, $this->getFilename ( $key ) );
	}

	public function getAll() {
		$files = UFileSystem::glob_recursive ( $this->folder . \DS . '*' );
		$result = [ ];
		foreach ( $files as $file ) {
			$result [] = include $file;
		}
		return $result;
	}

	public function get($key, $default = null) {
		$filename = $this->getFilename ( $key );
		if (\file_exists ( $filename )) {
			$f = \file_get_contents ( $filename );
			$val = \unserialize ( $f );
		}
		return isset ( $val ) ? $val : $default;
	}

	public function start($name = null, $root = null) {
		$this->name = $name;
		if (! isset ( $root )) {
			$this->folder = \ROOT . \DS . CacheManager::getCacheDirectory () . \DS . 'session' . \DS;
		} else {
			$this->folder = $root . \DS . 'session' . \DS;
		}
		if (isset ( $name )) {
			$this->folder .= $name . \DS;
		}
		if (! UCookie::exists ( self::SESSION_ID )) {
			$id = $this->generateId ();
			UCookie::set ( self::SESSION_ID, $id );
			$this->id = $id;
		} else {
			$this->id = UCookie::get ( self::SESSION_ID );
		}
		$this->verifyCsrf->start ();
		UFileSystem::safeMkdir ( $this->folder . $this->id . \DS );
	}

	public function exists($key) {
		return file_exists ( $this->getFilename ( $key ) );
	}

	public function terminate() {
		$this->verifyCsrf->clear ();
		UFileSystem::delTree ( $this->folder . $this->id . \DS );
	}

	public function isStarted() {
		return isset ( $this->id );
	}

	public function delete($key) {
		\unlink ( $this->getFilename ( $key ) );
	}
}

