<?php

namespace Ubiquity\controllers\traits;

use Ubiquity\utils\base\UString;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\http\foundation\PhpHttp;
use Ubiquity\utils\http\foundation\AbstractHttp;
use Ubiquity\utils\http\session\PhpSession;
use Ubiquity\utils\http\session\AbstractSession;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\CodeUtils;
use Ubiquity\orm\DAO;

/**
 * Ubiquity\controllers\traits$StartupConfigTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.2
 *
 */
trait StartupConfigTrait {
	public static $config;
	protected static $ctrlNS;
	protected static $httpInstance;
	protected static $sessionInstance;

	public static function getConfig() {
		return self::$config;
	}

	public static function setConfig($config) {
		self::$config = $config;
	}

	public static function getModelsDir() {
		return self::$config ['mvcNS'] ['models'];
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

	public static function getNS($part = 'controllers') {
		$ns = self::$config ['mvcNS'] [$part];
		return ($ns != null) ? $ns .= "\\" : $ns;
	}

	protected static function setCtrlNS() {
		return self::$ctrlNS = self::getNS ();
	}

	public static function checkDbConfig($offset = 'default') {
		$config = self::$config;
		$result = [ ];
		$needs = [ "type","dbName","serverName" ];
		if (! isset ( $config ["database"] )) {
			$result [] = "database";
		} else {
			self::needsKeyInConfigArray ( $result, DAO::getDbOffset ( $config, $offset ), $needs );
		}
		return $result;
	}

	public static function checkModelsConfig() {
		$config = self::$config;
		$result = [ ];
		if (! isset ( $config ['mvcNS'] )) {
			$result [] = "mvcNS";
		} else {
			self::needsKeyInConfigArray ( $result, $config ['mvcNS'], [ 'models' ] );
		}
		return $result;
	}

	public static function reloadConfig() {
		$appDir = \dirname ( \ROOT );
		$filename = $appDir . "/app/config/config.php";
		self::$config = include ($filename);
		self::startTemplateEngine ( self::$config );
		return self::$config;
	}

	public static function reloadServices() {
		$config = self::$config; // used in services.php
		include \ROOT . 'config/services.php';
	}

	public static function saveConfig(array $contentArray) {
		$appDir = \dirname ( \ROOT );
		$filename = $appDir . "/app/config/config.php";
		$oldFilename = $appDir . "/app/config/config.old.php";
		$content = "<?php\nreturn " . UArray::asPhpArray ( $contentArray, "array", 1, true ) . ";";
		if (CodeUtils::isValidCode ( $content )) {
			if (! file_exists ( $filename ) || copy ( $filename, $oldFilename )) {
				return UFileSystem::save ( $filename, $content );
			}
		} else {
			throw new \RuntimeException ( 'Config contains invalid code' );
		}
		return false;
	}

	public static function updateConfig(array $content) {
		foreach ( $content as $k => $v ) {
			self::$config [$k] = $v;
		}
		return self::saveConfig ( self::$config );
	}

	public static function getHttpInstance() {
		if (! isset ( self::$httpInstance )) {
			self::$httpInstance = new PhpHttp ();
		}
		return self::$httpInstance;
	}

	public static function setHttpInstance(AbstractHttp $httpInstance) {
		self::$httpInstance = $httpInstance;
	}

	public static function getSessionInstance() {
		if (! isset ( self::$sessionInstance )) {
			self::$sessionInstance = new PhpSession ();
		}
		return self::$sessionInstance;
	}

	public static function setSessionInstance(AbstractSession $sessionInstance) {
		self::$sessionInstance = $sessionInstance;
	}
}

