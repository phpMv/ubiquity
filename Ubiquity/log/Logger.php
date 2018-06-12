<?php

namespace Ubiquity\log;

abstract class Logger {
	/**
	 * @var Logger
	 */
	private static $instance;
	private static $test;

	private static function createLogger(&$config){
		if(is_callable($logger=$config["logger"])){
			$instance=$logger();
		}else{
			$instance=$config["logger"];
		}
		if($instance instanceof Logger){
			self::$instance=$instance;
		}
	}

	public static function init(&$config) {
		if(self::$test=isset($config["logger"]) && $config["debug"]===true){
			self::createLogger($config);
		}
	}

	public static function log($level,$context, $message,$part=null) {
		if (self::$test)
			return self::$instance->_log($level,$context, $message,$part) ;
	}
	
	public static function info($context, $message,$part=null) {
		if (self::$test)
			return self::$instance->_info($context, $message,$part) ;
	}

	public static function warn($context, $message,$part=null) {
		if (self::$test)
			return self::$instance->_warn($context, $message,$part) ;
	}

	public static function error($context, $message,$part=null) {
		if (self::$test)
			return self::$instance->_error($context, $message,$part) ;
	}
	
	public static function critical($context, $message,$part=null) {
		if (self::$test)
			return self::$instance->_critical($context, $message,$part) ;
	}
	
	public static function alert($context, $message,$part=null) {
		if (self::$test)
			return self::$instance->_alert($context, $message,$part) ;
	}

	public static function asObjects(){
		if (self::$test)
			return self::$instance->_asObjects();
		return [];
	}
	
	abstract public function _log($level,$context, $message,$part);
	abstract public function _info($context, $message,$part);
	abstract public function _warn($context, $message,$part);
	abstract public function _error($context, $message,$part);
	abstract public function _critical($context, $message,$part);
	abstract public function _alert($context, $message,$part);
	abstract  public function _asObjects();
}
