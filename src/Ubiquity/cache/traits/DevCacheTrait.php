<?php

namespace Ubiquity\cache\traits;

use Ubiquity\annotations\AnnotationsEngineInterface;
use Ubiquity\cache\system\AbstractDataCache;
use Ubiquity\config\Configuration;
use Ubiquity\utils\base\UFileSystem;

/**
 * To be Used in dev mode, not in production
 * Ubiquity\cache\traits$DevCacheTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.3
 *
 * @property string $cacheDirectory
 */
trait DevCacheTrait {

	private static AnnotationsEngineInterface $annotationsEngine;

	abstract protected static function getCacheInstance(array &$config, string $cacheDirectory, string $postfix): AbstractDataCache;

	abstract protected static function initRestCache(array &$config, bool $silent = false): void;

	abstract protected static function initRouterCache(array &$config, bool $silent = false): void;

	abstract public static function initModelsCache(array &$config, bool $forChecking = false, bool $silent = false): void;

	private static function _getAnnotationsEngineInstance(): ?AnnotationsEngineInterface {
		if (\class_exists('Ubiquity\\attributes\\AttributesEngine', true)) {
			return new \Ubiquity\attributes\AttributesEngine();
		} elseif (\class_exists('Ubiquity\\annotations\\AnnotationsEngine', true)) {
			return new \Ubiquity\annotations\AnnotationsEngine();
		}
	}

	public static function getAnnotationsEngineInstance(): ?AnnotationsEngineInterface {
		return self::$annotationsEngine ??= self::_getAnnotationsEngineInstance();
	}

	private static function initialGetCacheDirectory(array &$config): string {
		return $config['cache']['directory'] ??= 'cache' . \DS;
	}

	/**
	 * Starts the cache in dev mode, for generating the other caches
	 * Do not use in production
	 *
	 * @param array $config
	 */
	public static function start(array &$config) {
		self::$cacheDirectory = self::initialGetCacheDirectory($config);
		$cacheDirectory = \ROOT . \DS . self::$cacheDirectory;
		self::getAnnotationsEngineInstance()->start($cacheDirectory);
		self::getCacheInstance($config, $cacheDirectory, '.cache')->init();
	}

	/**
	 *
	 * @param array $nameClasses
	 *            an array of name=>class annotations
	 */
	public static function registerAnnotations(array $nameClasses): void {
		self::getAnnotationsEngineInstance()->registerAnnotations($nameClasses);
	}


	/**
	 * Checks the existence of cache subdirectories and returns an array of cache folders
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function checkCache(array &$config, bool $silent = false): array {
		$dirs = self::getCacheDirectories($config, $silent);
		foreach ($dirs as $dir) {
			self::safeMkdir($dir);
		}
		return $dirs;
	}

	/**
	 * Returns an associative array of cache folders (annotations, models, controllers, queries, views, seo, git, contents)
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function getCacheDirectories(array &$config, bool $silent = false): array {
		$cacheDirectory = self::initialGetCacheDirectory($config);
		$rootDS = \ROOT . \DS;
		if (!$silent) {
			echo "cache directory is " . UFileSystem::cleanPathname($rootDS . $cacheDirectory) . "\n";
		}
		$cacheDirectory = $rootDS . $cacheDirectory . \DS;
		$modelsDir = \str_replace("\\", \DS, $config['mvcNS']['models']);
		$controllersDir = \str_replace("\\", \DS, $config['mvcNS']['controllers']);
		$annotationCacheDir = $cacheDirectory . 'annotations';
		$modelsCacheDir = $cacheDirectory . $modelsDir;
		$queriesCacheDir = $cacheDirectory . 'queries';
		$controllersCacheDir = $cacheDirectory . $controllersDir;
		$viewsCacheDir = $cacheDirectory . 'views';
		$seoCacheDir = $cacheDirectory . 'seo';
		$gitCacheDir = $cacheDirectory . 'git';
		$contentsCacheDir = $cacheDirectory . 'contents';
		$configCacheDir = $cacheDirectory . 'config';
		return [
			'annotations' => $annotationCacheDir,
			'models' => $modelsCacheDir,
			'controllers' => $controllersCacheDir,
			'queries' => $queriesCacheDir,
			'views' => $viewsCacheDir,
			'seo' => $seoCacheDir,
			'git' => $gitCacheDir,
			'contents' => $contentsCacheDir,
			'config' => $configCacheDir
		];
	}

	private static function safeMkdir(string $dir): bool {
		if (!\is_dir($dir)) {
			return \mkdir($dir, 0777, true);
		}
		return true;
	}

	/**
	 * Deletes files from a cache type
	 *
	 * @param array $config
	 * @param string $type
	 */
	public static function clearCache(array &$config, string $type = 'all') {
		$cacheDirectories = self::checkCache($config);
		$cacheDirs = [
			'annotations',
			'controllers',
			'models',
			'queries',
			'views',
			'contents',
			'config'
		];
		foreach ($cacheDirs as $typeRef) {
			self::_clearCache($cacheDirectories, $type, $typeRef);
		}
	}

	private static function _clearCache($cacheDirectories, $type, $typeRef) {
		if ($type === 'all' || $type === $typeRef) {
			UFileSystem::deleteAllFilesFromFolder($cacheDirectories[$typeRef]);
		}
	}

	/**
	 *
	 * @param array $config
	 * @param string $type
	 * @param boolean $silent
	 */
	public static function initCache(array &$config, string $type = 'all', bool $silent = false): void {
		self::checkCache($config, $silent);
		self::start($config);
		if ($type === 'all' || $type === 'models') {
			self::initModelsCache($config, false, $silent);
		}
		if ($type === 'all' || $type === 'controllers') {
			if (\class_exists('\\Ubiquity\\security\\acl\\AclManager')) {
				self::getAnnotationsEngineInstance()->registerAcls();
			}
			self::initRouterCache($config, $silent);
		}
		if ($type === 'all' || $type === 'acls') {
			if (\class_exists('\\Ubiquity\\security\\acl\\AclManager')) {
				\Ubiquity\security\acl\AclManager::initCache($config);
			}
		}
		if ($type === 'all' || $type === 'rest') {
			self::initRestCache($config, $silent);
		}
		if ($type === 'all' || $type === 'config') {
			Configuration::generateCache($silent);
		}
	}
}

