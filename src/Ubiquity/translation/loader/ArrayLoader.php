<?php

namespace Ubiquity\translation\loader;

use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\base\UArray;
use Ubiquity\log\Logger;
use Ubiquity\cache\CacheManager;

/**
 * ArrayLoader for TranslatorManager.
 * Ubiquity\translation\loader$ArrayLoader
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class ArrayLoader implements LoaderInterface {
	private $rootDir;
	private $key = 'translations/';

	private function getRootKey($locale = null, $domain = null) {
		return $this->key . $locale ?? '' . $domain ?? '';
	}

	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
	}

	public function loadDomain($locale, $domain) {
		$messages = [ ];
		$rootDirectory = $this->getRootDirectory ( $locale );
		if (\file_exists ( $rootDirectory )) {
			$filename = $rootDirectory . \DS . $domain . '.php';
			if (\file_exists ( $filename )) {
				$messages = $this->loadFile ( $filename );
			}
		}
		return $messages;
	}

	public function load($locale, $domain = '*') {
		$key = $this->getRootKey ( $locale, $domain );
		if (CacheManager::$cache->exists ( $key )) {
			return CacheManager::$cache->fetch ( $key );
		}
		$messages = [ ];
		$rootDirectory = $this->getRootDirectory ( $locale );
		if (\file_exists ( $rootDirectory )) {
			$files = UFileSystem::glob_recursive ( $rootDirectory . $domain . '.php' );
			foreach ( $files as $file ) {
				if (\file_exists ( $file )) {
					$name = \basename ( $file, '.php' );
					Logger::info ( 'Translate', 'Loading ' . $locale . '.' . $domain . ' from file ' . $name, 'load', [ \get_class () ] );
					$messages [$name] = $this->loadFile ( $file );
				}
			}
			$this->flatten ( $messages );
			CacheManager::$cache->store ( $key, $messages );
		} else {
			return false;
		}

		return $messages;
	}

	public function clearCache($locale = null, $domain = null) {
		if (isset ( $locale )) {
			CacheManager::$cache->remove ( $this->getRootKey ( $locale, $domain ) );
		} else {
			CacheManager::$cache->clearCache ( $this->getRootKey ( $locale, $domain ) );
		}
	}

	protected function loadFile($filename) {
		return include $filename;
	}

	private function getRootDirectory($locale) {
		return $this->rootDir . \DS . $locale . \DS;
	}

	private function getDirectory($domain, &$filename) {
		$parts = \explode ( '.', $domain );
		$filename = \array_pop ( $parts ) . ".php";
		return \implode ( \DS, $parts );
	}

	/**
	 * Flattens an nested array of translations.
	 *
	 * The scheme used is:
	 * 'key' => array('key2' => array('key3' => 'value'))
	 * Becomes:
	 * 'key.key2.key3' => 'value'
	 *
	 * This function takes an array by reference and will modify it
	 *
	 * @param array &$messages The array that will be flattened
	 * @param array $subnode Current subnode being parsed, used internally for recursive calls
	 * @param string $path Current path being parsed, used internally for recursive calls
	 */
	private function flatten(array &$messages, array $subnode = null, $path = null) {
		if (null === $subnode) {
			$subnode = &$messages;
		}
		foreach ( $subnode as $key => $value ) {
			if (\is_array ( $value )) {
				$nodePath = $path ? $path . '.' . $key : $key;
				$this->flatten ( $messages, $value, $nodePath );
				if (null === $path) {
					unset ( $messages [$key] );
				}
			} elseif (null !== $path) {
				$messages [$path . '.' . $key] = $value;
			}
		}
	}

	public function save($messages, $locale, $domain) {
		$content = "<?php\nreturn " . UArray::asPhpArray ( $messages, 'array' ) . ';';
		$filename = "";
		$path = $this->getRootDirectory ( $locale ) . $this->getDirectory ( $domain, $filename );
		if (UFileSystem::safeMkdir ( $path )) {
			if (@\file_put_contents ( $path . \DS . $filename, $content, LOCK_EX ) === false) {
				throw new \Exception ( "Unable to write cache file: {$filename}" );
			}
		} else {
			throw new \Exception ( "Unable to create folder : {$path}" );
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getRootDir() {
		return $this->rootDir;
	}

	public function getDomains($locale) {
		$domains = [ ];
		$rootDirectory = $this->getRootDirectory ( $locale );
		if (\file_exists ( $rootDirectory )) {
			$files = UFileSystem::glob_recursive ( $rootDirectory . '*.php' );
			foreach ( $files as $file ) {
				$domains [] = \basename ( $file, '.php' );
			}
		}
		return $domains;
	}

	public function cacheExists($locale, $domain = '*') {
		$key = $this->getRootKey ( $locale, $domain );
		return CacheManager::$cache->exists ( $key );
	}
}
