<?php

namespace Ubiquity\log;

/**
 * Abstract class for logging
 * Ubiquity\log$Logger
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
abstract class Logger {
	/**
	 *
	 * @var Logger
	 */
	private static $instance;
	private static $test;

	private static function createLogger(&$config) {
		if (\is_callable ( $logger = $config ["logger"] )) {
			$instance = $logger ();
		} else {
			$instance = $config ["logger"];
		}
		if ($instance instanceof Logger) {
			self::$instance = $instance;
		}
	}

	abstract public function _registerError();

	public static function registerError() {
		self::$instance->_registerError ();
	}

	public static function inContext($contexts, $context) {
		if ($contexts === null) {
			return true;
		}
		return array_search ( $context, $contexts ) !== false;
	}

	public static function init(&$config) {
		if ($config ["debug"] === true && (self::$test = isset ( $config ["logger"] ))) {
			self::createLogger ( $config );
		}
	}

	public static function log($level, $context, $message, $part = null, $extra = null) {
		if (self::$test)
			self::$instance->_log ( $level, $context, $message, $part, $extra );
	}

	public static function info($context, $message, $part = null, $extra = null) {
		if (self::$test)
			self::$instance->_info ( $context, $message, $part, $extra );
	}

	public static function warn($context, $message, $part = null, $extra = null) {
		if (self::$test)
			self::$instance->_warn ( $context, $message, $part, $extra );
	}

	public static function error($context, $message, $part = null, $extra = null) {
		if (self::$test)
			self::$instance->_error ( $context, $message, $part, $extra );
	}

	public static function critical($context, $message, $part = null, $extra = null) {
		if (self::$test)
			self::$instance->_critical ( $context, $message, $part, $extra );
	}

	public static function alert($context, $message, $part = null, $extra = null) {
		if (self::$test)
			self::$instance->_alert ( $context, $message, $part, $extra );
	}

	public static function asObjects($reverse = true, $maxlines = 10, $contexts = null) {
		if (isset ( self::$instance ) && self::$test)
			return self::$instance->_asObjects ( $reverse, $maxlines, $contexts );
		return [ ];
	}

	public static function clearAll() {
		if (self::$test) {
			self::$instance->_clearAll ();
		}
	}

	public static function close() {
		if (self::$test) {
			self::$instance->_close ();
		}
	}

	abstract public function _log($level, $context, $message, $part, $extra);

	abstract public function _info($context, $message, $part, $extra);

	abstract public function _warn($context, $message, $part, $extra);

	abstract public function _error($context, $message, $part, $extra);

	abstract public function _critical($context, $message, $part, $extra);

	abstract public function _alert($context, $message, $part, $extra);

	abstract public function _asObjects($reverse = true, $maxlines = 10, $contexts = null);

	abstract public function _clearAll();

	abstract public function _close();

	public static function isActive() {
		return self::$test;
	}
}
