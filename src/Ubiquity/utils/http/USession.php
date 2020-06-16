<?php

namespace Ubiquity\utils\http;

use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\session\SessionObject;
use Ubiquity\controllers\Startup;

/**
 * Http Session utilities
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.2
 */
class USession {
	protected static $sessionInstance;

	/**
	 * Returns an array stored in session variable as $arrayKey
	 *
	 * @param string $arrayKey the key of the array to return
	 * @return array
	 */
	public static function getArray($arrayKey): array {
		if (self::$sessionInstance->exists ( $arrayKey )) {
			$array = self::$sessionInstance->get ( $arrayKey );
			if (! \is_array ( $array ))
				$array = [ ];
		} else
			$array = [ ];
		return $array;
	}

	/**
	 * Adds or removes a value from an array in session
	 *
	 * @param string $arrayKey the key of the array to add or remove in
	 * @param mixed $value the value to add
	 * @param boolean|null $add If true, adds otherwise removes
	 * @return boolean|null
	 */
	public static function addOrRemoveValueFromArray($arrayKey, $value, $add = null): ?bool {
		$array = self::getArray ( $arrayKey );
		$_SESSION [$arrayKey] = $array;
		$search = \array_search ( $value, $array );
		if ($search === false && $add) {
			$array [] = $value;
			self::$sessionInstance->set ( $arrayKey, $array );
			return true;
		} else if ($add !== true) {
			unset ( $array [$search] );
			self::$sessionInstance->set ( $arrayKey, $array );
			return false;
		}
		return null;
	}

	/**
	 * Removes a value from an array in session
	 *
	 * @param string $arrayKey the key of the array to remove in
	 * @param mixed $value the value to remove
	 * @return boolean|null
	 */
	public static function removeValueFromArray($arrayKey, $value): ?bool {
		return self::addOrRemoveValueFromArray ( $arrayKey, $value, false );
	}

	/**
	 * Adds a value from an array in session
	 *
	 * @param string $arrayKey the key of the array to add in
	 * @param mixed $value the value to add
	 * @return boolean|null
	 */
	public static function addValueToArray($arrayKey, $value): ?bool {
		return self::addOrRemoveValueFromArray ( $arrayKey, $value, true );
	}

	/**
	 * Sets a boolean value at key position in session
	 *
	 * @param string $key the key to add or set in
	 * @param mixed $value the value to set
	 * @return boolean
	 */
	public static function setBoolean($key, $value) {
		return self::$sessionInstance->set ( $key, UString::isBooleanTrue ( $value ) );
	}

	/**
	 * Returns a boolean stored at the key position in session
	 *
	 * @param string $key the key to add or set
	 * @return boolean
	 */
	public static function getBoolean($key): bool {
		$v = self::$sessionInstance->get ( $key, false );
		return UString::isBooleanTrue ( $v );
	}

	/**
	 * Returns the value stored at the key position in session
	 *
	 * @param string $key the key to retreive
	 * @param mixed $default the default value to return if the key does not exists in session
	 * @return mixed
	 */
	public static function session($key, $default = NULL) {
		return self::$sessionInstance->get ( $key, $default );
	}

	/**
	 * Returns the value stored at the key position in session
	 *
	 * @param string $key the key to retreive
	 * @param mixed $default the default value to return if the key does not exists in session
	 * @return mixed
	 */
	public static function get($key, $default = NULL) {
		return self::$sessionInstance->get ( $key, $default );
	}

	/**
	 * Adds or sets a value to the Session at position $key
	 *
	 * @param string $key the key to add or set
	 * @param mixed $value
	 */
	public static function set($key, $value) {
		return self::$sessionInstance->set ( $key, $value );
	}

	public static function setTmp($key, $value, $duration) {
		if (self::$sessionInstance->exists ( $key )) {
			$object = self::$sessionInstance->get ( $key );
			if ($object instanceof SessionObject) {
				return $object->setValue ( $value );
			}
		}
		$object = new SessionObject ( $value, $duration );
		return self::$sessionInstance->set ( $key, $object );
	}

	public static function getTmp($key, $default = null) {
		if (self::$sessionInstance->exists ( $key )) {
			$object = self::$sessionInstance->get ( $key );
			if ($object instanceof SessionObject) {
				$value = $object->getValue ();
				if (isset ( $value ))
					return $object->getValue ();
				else {
					self::delete ( $key );
				}
			}
		}
		return $default;
	}

	public static function getTimeout($key) {
		if (self::$sessionInstance->exists ( $key )) {
			$object = self::$sessionInstance->get ( $key );
			if ($object instanceof SessionObject) {
				$value = $object->getTimeout ();
				if ($value < 0) {
					return 0;
				} else {
					return $value;
				}
			}
		}
		return;
	}

	/**
	 * Deletes the key in Session
	 *
	 * @param string $key the key to delete
	 */
	public static function delete($key): void {
		self::$sessionInstance->delete ( $key );
	}

	/**
	 * Increment the value at the key index in session
	 *
	 * @param string $key
	 * @param number $inc
	 * @return number
	 */
	public static function inc($key, $inc = 1) {
		return self::set ( $key, self::get ( $key, 0 ) + $inc );
	}

	/**
	 * Decrement the value at the key index in session
	 *
	 * @param string $key
	 * @param number $dec
	 * @return number
	 */
	public static function dec($key, $dec = 1) {
		return self::set ( $key, self::get ( $key, 0 ) - $dec );
	}

	/**
	 * Adds a string at the end of the value at the key index in session
	 *
	 * @param string $key
	 * @param string $str
	 * @return string
	 */
	public static function concat($key, $str, $default = NULL): string {
		return self::set ( $key, self::get ( $key, $default ) . $str );
	}

	/**
	 * Applies a callback function to the value at the key index in session
	 *
	 * @param string $key
	 * @param string|callable $callback
	 * @return mixed
	 */
	public static function apply($key, $callback, $default = NULL) {
		$value = self::get ( $key, $default );
		if (\is_string ( $callback ) && \function_exists ( $callback )) {
			$value = \call_user_func ( $callback, $value );
		} elseif ($callback instanceof \Closure) {
			$value = $callback ( $value );
		} else {
			return $value;
		}
		return self::set ( $key, $value );
	}

	/**
	 * Apply a user supplied function to every member of Session array
	 *
	 * @param callable $callback
	 * @param mixed $userData
	 * @return array
	 */
	public static function Walk($callback, $userData = null): array {
		$all = self::$sessionInstance->getAll ();
		foreach ( $all as $k => $v ) {
			self::$sessionInstance->set ( $k, $callback ( $k, $v, $userData ) );
		}
		return self::$sessionInstance->getAll ();
	}

	/**
	 * Replaces elements from Session array with $keyAndValues
	 *
	 * @param array $keyAndValues
	 * @return array
	 */
	public static function replace($keyAndValues): array {
		foreach ( $keyAndValues as $k => $v ) {
			self::$sessionInstance->set ( $k, $v );
		}
		return self::$sessionInstance->getAll ();
	}

	/**
	 * Returns the associative array of session vars
	 *
	 * @return array
	 */
	public static function getAll(): array {
		return self::$sessionInstance->getAll ();
	}

	/**
	 * Start new or resume existing session
	 *
	 * @param string|null $name the name of the session
	 */
	public static function start($name = null): void {
		if (! isset ( self::$sessionInstance )) {
			self::$sessionInstance = Startup::getSessionInstance ();
		}
		self::$sessionInstance->start ( $name );
	}

	/**
	 * Returns true if the session is started
	 *
	 * @return boolean
	 */
	public static function isStarted(): bool {
		return self::$sessionInstance->isStarted ();
	}

	/**
	 * Returns true if the key exists in Session
	 *
	 * @param string $key the key to test
	 * @return boolean
	 */
	public static function exists($key): bool {
		return self::$sessionInstance->exists ( $key );
	}

	/**
	 * Initialize the key in Session if key does not exists
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	public static function init($key, $value) {
		if (! self::$sessionInstance->exists ( $key )) {
			self::$sessionInstance->set ( $key, $value );
		}
		return self::$sessionInstance->get ( $key );
	}

	/**
	 * Terminates the active session
	 */
	public static function terminate(): void {
		self::$sessionInstance->terminate ();
	}

	/**
	 * Return the Csrf protection class name.
	 *
	 * @return string
	 */
	public static function getCsrfProtectionClass() {
		if (isset ( self::$sessionInstance )) {
			return \get_class ( self::$sessionInstance->getVerifyCsrf () );
		}
	}

	/**
	 * Return the instance class name for the session.
	 *
	 * @return string
	 */
	public static function getInstanceClass() {
		if (isset ( self::$sessionInstance )) {
			return \get_class ( self::$sessionInstance );
		}
	}

	/**
	 * Returns the number of sessions started.
	 *
	 * @return number
	 */
	public static function visitorCount() {
		return self::$sessionInstance->visitorCount ();
	}
}
