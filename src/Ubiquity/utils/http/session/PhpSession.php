<?php

namespace Ubiquity\utils\http\session;

/**
 * Default php session.
 * Ubiquity\utils\http\session$PhpSession
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class PhpSession extends AbstractSession {

	public function set(string $key, $value) {
		return $_SESSION [$key] = $value;
	}

	public function get(string $key, $default = null) {
		return $_SESSION [$key] ?? $default;
	}

	public function start(string $name = null) {
		if (! $this->isStarted ()) {
			if (isset ( $name ) && $name !== '') {
				$this->name = $name;
			}
			if (isset ( $this->name )) {
				\session_name ( $this->name );
			}
			\session_start ();
			$this->verifyCsrf->start ();
		}
	}

	public function terminate(): void {
		if (! $this->isStarted ()) {
			return;
		}
		$this->verifyCsrf->clear ();
		$_SESSION = [];

		if (\ini_get ( 'session.use_cookies' )) {
			$params = \session_get_cookie_params ();
			\setcookie ( \session_name (), '', \time () - 42000, $params ['path'], $params ['domain'], $params ['secure'], $params ['httponly'] );
		}
		\session_destroy ();
	}

	public function isStarted(): bool {
		return \session_status () == PHP_SESSION_ACTIVE;
	}

	public function exists(string $key): bool {
		return isset ( $_SESSION [$key] );
	}

	public function getAll(): array {
		return $_SESSION;
	}

	public function delete(string $key) {
		unset ( $_SESSION [$key] );
	}
	
	public function regenerateId(bool $deleteOldSession=false):bool {
		return \session_regenerate_id($deleteOldSession);
	}

	public function visitorCount(): int {
		$sessionPath = \ini_get ( 'session.save_path' );
		$sessionLifetime = \ini_get ( 'session.gc_maxlifetime' );
		$files = glob ( $sessionPath . DS . 'sess_*' );
		$now = time ();
		$count = 0;
		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				if ($now - filemtime ( $file ) <= $sessionLifetime) {
					$count ++;
				}
			}
		}
		return $count;
	}
}

