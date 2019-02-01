<?php

namespace Ubiquity\controllers\traits;

use Ubiquity\utils\base\UString;
use Ubiquity\utils\base\UFileSystem;

trait StartupConfigTrait {
	protected static $config;
	protected static $ctrlNS;
	
	public static function getConfig() {
		return self::$config;
	}
	
	public static function setConfig($config) {
		self::$config = $config;
	}
	
	public static function getModelsDir() {
		return self::$config ["mvcNS"] ["models"];
	}
	
	public static function getModelsCompletePath() {
		return \ROOT . \DS . self::getModelsDir ();
	}
	
	protected static function needsKeyInConfigArray(&$result, $array, $needs) {
		foreach ( $needs as $need ) {
			if (! isset ( $array [$need] ) || UString::isNull ( $array [$need] )) {
				$result [] = $need;
			}
		}
	}
	
	public static function getNS($part = "controllers") {
		$config = self::$config;
		$ns = $config ["mvcNS"] [$part];
		if ($ns !== "" && $ns !== null) {
			$ns .= "\\";
		}
		return $ns;
	}
	
	protected static function setCtrlNS() {
		self::$ctrlNS = self::getNS ();
	}
	
	public static function checkDbConfig() {
		$config = self::$config;
		$result = [ ];
		$needs = [ "type","dbName","serverName" ];
		if (! isset ( $config ["database"] )) {
			$result [] = "database";
		} else {
			self::needsKeyInConfigArray ( $result, $config ["database"], $needs );
		}
		return $result;
	}
	
	public static function checkModelsConfig() {
		$config = self::$config;
		$result = [ ];
		if (! isset ( $config ["mvcNS"] )) {
			$result [] = "mvcNS";
		} else {
			self::needsKeyInConfigArray ( $result, $config ["mvcNS"], [ "models" ] );
		}
		return $result;
	}
	
	
	public static function reloadConfig(){
		$appDir=\dirname ( \ROOT );
		$filename=$appDir."/app/config/config.php";
		self::$config=include($filename);
		self::startTemplateEngine(self::$config);
		return self::$config;
	}
	
	public static function saveConfig($content){
		$appDir=\dirname ( \ROOT );
		$filename=$appDir."/app/config/config.php";
		$oldFilename=$appDir."/app/config/config.old.php";
		if (!file_exists($filename) || copy($filename, $oldFilename)) {
			return UFileSystem::save($filename,$content);
		}
		return false;
	}
}

