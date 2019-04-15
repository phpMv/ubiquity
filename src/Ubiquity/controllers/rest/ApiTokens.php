<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\cache\system\ArrayCache;
use Ubiquity\utils\base\UArray;

/**
 * Manage the token api for the Rest part.
 * Ubiquity\controllers\rest$ApiTokens
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class ApiTokens {
	protected $tokens;
	protected $length;
	protected $duration;
	protected static $cache;

	public function __construct($length = 10, $duration = 3600, $tokens = []) {
		$this->length = $length;
		$this->duration = $duration;
		$this->tokens = $tokens;
	}

	protected function generateToken() {
		do {
			$token = $this->tokenGenerator ();
		} while ( \array_search ( $token, $this->tokens, true ) === true );
		return $token;
	}

	protected function tokenGenerator() {
		return \bin2hex ( \random_bytes ( $this->length ) );
	}

	public function getTokens() {
		return $this->tokens;
	}

	public function getDuration() {
		return $this->duration;
	}

	public function getToken($key) {
		if (isset ( $this->tokens [$key] ))
			return $this->tokens [$key];
		return false;
	}

	public function isExpired($key) {
		$token = $this->getToken ( $key );
		if ($token !== false)
			return \time () - $token ["creationTime"] > $this->duration;
		return true;
	}

	public function addToken() {
		$key = $this->generateToken ();
		$this->tokens [$key] = [ "creationTime" => \time () ];
		return $key;
	}

	public function clearAll() {
		$this->tokens = [ ];
	}

	public function removeExpireds() {
		$tokens = $this->tokens;
		foreach ( $tokens as $key => $value ) {
			if ($this->isExpired ( $key )) {
				unset ( $this->tokens [$key] );
			}
		}
	}

	public function remove($key) {
		if (isset ( $this->tokens [$key] )) {
			unset ( $this->tokens [$key] );
			return true;
		}
		return false;
	}

	public function storeToCache($key = "_apiTokens") {
		$fileContent = [ "duration" => $this->duration,"length" => $this->length,"tokens" => $this->tokens ];
		self::$cache->store ( $key, "return " . UArray::asPhpArray ( $fileContent, "array" ) . ";" );
	}

	/**
	 *
	 * @param $folder
	 * @param string $key
	 * @param number $length
	 * @param number $duration
	 * @return ApiTokens
	 */
	public function getFromCache($folder, $key = "_apiTokens", $length = 10, $duration = 3600) {
		if (! isset ( self::$cache )) {
			self::$cache = new ArrayCache ( $folder . "rest/tokens", ".rest" );
		}
		$tokens = [ ];
		if (self::$cache->exists ( $key )) {
			$filecontent = self::$cache->fetch ( $key );
			if (isset ( $filecontent ["tokens"] )) {
				$tokens = $filecontent ["tokens"];
			}
			if (isset ( $filecontent ["length"] )) {
				$length = $filecontent ["length"];
			}
			if (isset ( $filecontent ["duration"] )) {
				$duration = $filecontent ["duration"];
			}
		}
		$this->length=$length;
		$this->duration=$duration;
		$this->tokens=$tokens;
		return $this;
	}
}
