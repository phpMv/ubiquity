<?php

namespace Ubiquity\log;

abstract class Logger {
	/**
	 * @var Logger
	 */
	private static $instance;
	private static $test;

	private static function createLogger(&$config){
		self::$instance=null;
	}

	public static function init(&$config) {
		if(self::$test=isset($config["logger"]) && $config["logger"]){
			self::createLogger($config);
		}
	}

	public static function log($id, $message,$code=0) {
		if (self::$test)
			self::$instance->_log($id, $message, $code) ;
	}

	public static function warn($id, $message,$code=0) {
		if (self::$test)
			self::$instance->_warn($id, $message, $code) ;
	}

	public static function error($id, $message,$code=0) {
		if (self::$test)
			self::$instance->_error($id, $message, $code) ;
	}

	abstract public function _log($id,$message,$code);
	abstract public function _warn($id,$message,$code);
	abstract public function _error($id,$message,$code);
}
