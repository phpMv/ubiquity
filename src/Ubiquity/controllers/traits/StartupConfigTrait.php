<?php

namespace Ubiquity\controllers\traits;

use Ubiquity\controllers\Router;
use Ubiquity\exceptions\InvalidCodeException;
use Ubiquity\orm\DAO;
use Ubiquity\utils\base\CodeUtils;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\foundation\AbstractHttp;
use Ubiquity\utils\http\foundation\PhpHttp;
use Ubiquity\utils\http\session\AbstractSession;
use Ubiquity\utils\http\session\PhpSession;

/**
 * Ubiquity\controllers\traits$StartupConfigTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.8
 *
 */
trait StartupConfigTrait {
	public static $config;
	protected static $ctrlNS;
	protected static $httpInstance;
	protected static $sessionInstance;
	protected static $activeDomainBase = '';
	protected static $CONFIG_LOCATION = '/config/';
	protected static $CONFIG_CACHE_LOCATION = 'cache/config/config.cache.php';

	public static function getConfig(): array {
		return self::$config;
	}

	public static function setConfig($config): void {
		self::$config = $config;
	}

	public static function getModelsDir(): string {
		return \str_replace('\\', \DS, self::getNS('models'));
	}

	public static function getModelsCompletePath(): string {
		return \ROOT . \DS . self::getModelsDir();
	}

	protected static function needsKeyInConfigArray(&$result, $array, $needs): void {
		foreach ($needs as $need) {
			if (!isset ($array [$need]) || UString::isNull($array [$need])) {
				$result [] = $need;
			}
		}
	}

	public static function getNS($part = 'controllers'): string {
		return self::$activeDomainBase . ((self::$config ['mvcNS'] [$part]) ?? $part) . "\\";
	}

	protected static function setCtrlNS(): string {
		return self::$ctrlNS = self::getNS();
	}

	public static function checkDbConfig($offset = 'default'): array {
		$config = self::$config;
		$result = [];
		$needs = ['type', 'dbName', 'serverName'];
		if (!isset ($config ['database'])) {
			$result [] = 'database';
		} else {
			self::needsKeyInConfigArray($result, DAO::getDbOffset($config, $offset), $needs);
		}
		return $result;
	}

	public static function checkModelsConfig(): array {
		$config = self::$config;
		$result = [];
		if (!isset ($config ['mvcNS'])) {
			$result [] = 'mvcNS';
		} else {
			self::needsKeyInConfigArray($result, $config ['mvcNS'], ['models']);
		}
		return $result;
	}

	public static function reloadConfig(bool $preserveSiteUrl = true): array {
		$siteUrl = self::$config['siteUrl'];
		$filename = \ROOT . self::$CONFIG_CACHE_LOCATION;
		self::$config = include($filename);
		if ($preserveSiteUrl) {
			self::$config ['siteUrl'] = $siteUrl;
		}
		self::startTemplateEngine(self::$config);
		return self::$config;
	}

	public static function reloadServices(): void {
		$config = self::$config; // used in services.php
		include \ROOT . 'config/services.php';
	}

	public static function saveConfig(array $contentArray, string $configFilename = 'config') {
		$appDir = \dirname(\ROOT);
		$filename = $appDir . "/app/config/$configFilename.php";
		$oldFilename = $appDir . '/app/config/config.old.php';
		$content = "<?php\nreturn " . UArray::asPhpArray($contentArray, 'array', 1, true) . ";";
		if (CodeUtils::isValidCode($content)) {
			if (!\file_exists($filename) || \copy($filename, $oldFilename)) {
				return UFileSystem::save($filename, $content);
			}
		} else {
			throw new InvalidCodeException ('Config contains invalid code');
		}
		return false;
	}

	public static function updateConfig(array $content, string $configFilename = 'config') {
		foreach ($content as $k => $v) {
			self::$config [$k] = $v;
		}
		return self::saveConfig(self::$config, $configFilename);
	}

	public static function getHttpInstance(): AbstractHttp {
		if (!isset (self::$httpInstance)) {
			self::$httpInstance = new PhpHttp ();
		}
		return self::$httpInstance;
	}

	public static function setHttpInstance(AbstractHttp $httpInstance): void {
		self::$httpInstance = $httpInstance;
	}

	public static function getSessionInstance(): AbstractSession {
		if (!isset (self::$sessionInstance)) {
			self::$sessionInstance = new PhpSession ();
		}
		return self::$sessionInstance;
	}

	public static function setSessionInstance(AbstractSession $sessionInstance): void {
		self::$sessionInstance = $sessionInstance;
	}

	public static function isValidUrl(string $url): bool {
		$u = self::parseUrl($url);
		if (\is_array(Router::getRoutes()) && ($ru = Router::getRoute($url, false, self::$config ['debug'] ?? false)) !== false) {
			if (\is_array($ru)) {
				if (\is_string($ru ['controller'])) {
					return static::isValidControllerAction($ru ['controller'], $ru ['action'] ?? 'index');
				} else {
					return \is_callable($ru);
				}
			}
		} else {
			$u [0] = self::$ctrlNS . $u [0];
			return static::isValidControllerAction($u [0], $u [1] ?? 'index');
		}
		return false;
	}

	private static function isValidControllerAction(string $controller, string $action): bool {
		if (\class_exists($controller)) {
			return \method_exists($controller, $action);
		}
		return false;
	}

	/**
	 * Sets the active domain for a Domain Driven Design approach.
	 * @param string $domain The new active domain name
	 * @param string $base The base folder for domains
	 */
	public static function setActiveDomainBase(string $domain, string $base = 'domains'): void {
		self::$activeDomainBase = $base . '\\' . \trim($domain, '\\') . '\\';
		if (isset(self::$templateEngine)) {
			$viewFolder = \realpath(\str_replace('\\', \DS, \ROOT . self::$activeDomainBase . 'views'));
			self::$templateEngine->setPaths([$viewFolder], $domain);
		}
	}

	public static function getActiveDomainBase(): string {
		return self::$activeDomainBase;
	}

	public static function resetActiveDomainBase(): void {
		self::$activeDomainBase = '';
	}
}

