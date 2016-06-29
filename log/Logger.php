<?php
namespace micro\log;
require_once ROOT.DS.'micro/log/chromePhp.php';

class Logger{
	public static $test;
	public static function init(){
		Logger::$test=$GLOBALS["config"]["test"];
		\ChromePhp::getInstance()->addSetting(\ChromePhp::BACKTRACE_LEVEL, 2);
	}
	public static function log($id,$message){
		if(Logger::$test)
		\ChromePhp::log($id.":".$message);
	}
	public static function warn($id,$message){
		if(Logger::$test)
		\ChromePhp::warn($id.":".$message);
	}
	public static function error($id,$message){
		if(Logger::$test)
		\ChromePhp::error($id.":".$message);
	}
}