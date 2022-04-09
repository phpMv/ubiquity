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
 * @version 1.0.4-beta
 *
 */
class MultisiteSession extends AbstractSession {
	private string $folder;
	private string $id;
	const SESSION_ID = 'multi_session_id';

	private function getKey($key): string {
		return \md5 ( $key );
	}

	private function getFilename($key): string {
		return $this->folder . $this->id . \DS . $this->getKey ( $key ) . '.cache.ser';
	}

	protected function generateId(): string {
		return \bin2hex ( \random_bytes ( 32 ) );
	}

	public function set(string $key, $value) {
		$val = \serialize ( $value );
		$tmp = "/tmp/$key." . \uniqid ( '', true ) . '.tmp';
		\file_put_contents ( $tmp, $val, LOCK_EX );
		\rename ( $tmp, $this->getFilename ( $key ) );
	}

	public function getAll(): array {
		$files = UFileSystem::glob_recursive ( $this->folder . \DS . '*' );
		$result = [ ];
		foreach ( $files as $file ) {
			$result [] = include $file;
		}
		return $result;
	}

	public function get(string $key, $default = null) {
		$filename = $this->getFilename ( $key );
		if (\file_exists ( $filename )) {
			$f = \file_get_contents ( $filename );
			$val = \unserialize ( $f );
		}
		return isset ( $val ) ? $val : $default;
	}

	public function start(string $name = null, $root = null) {
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

	public function exists($key): bool {
		return file_exists ( $this->getFilename ( $key ) );
	}

	public function terminate(): void {
		$this->verifyCsrf->clear ();
		UFileSystem::delTree ( $this->folder . $this->id . \DS );
	}

	public function isStarted(): bool {
		return isset ( $this->id );
	}

	public function delete($key) {
		\unlink ( $this->getFilename ( $key ) );
	}
	
	public function regenerateId(bool $deleteOldSession=false): bool {
		if($deleteOldSession){
			$this->terminate();
			$this->start($this->name,\rtrim($this->folder,\DS . 'session' . \DS));
			return true;
		}
		$newId=$this->generateId();
		$this->verifyCsrf->clear();
		UCookie::set ( self::SESSION_ID, $newId );
		$oldId=$this->id;
		$this->id = $newId;
		$this->verifyCsrf->start ();
		return UFileSystem::xmove($this->folder . $oldId . \DS, $this->folder . $newId . \DS);
	}

	public function visitorCount(): int {
		return \count ( \scandir ( $this->folder . $this->id . \DS ) );
	}
}
