<?php
namespace Ubiquity\cache\system;

use Ubiquity\utils\base\UFileSystem;
use Ubiquity\exceptions\CacheException;
use Ubiquity\cache\CacheFile;
use Ubiquity\utils\base\UArray;

/**
 * This class is responsible for storing Arrays in a PHP classe.
 * Ubiquity\cache\system$ObjectCache
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class ObjectCache extends AbstractDataCache {

	/**
	 *
	 * @var int The file mode used when creating new cache files
	 */
	private $_fileMode;

	/**
	 * Initializes the file cache-provider
	 *
	 * @param string $root
	 *        	absolute path to the root-folder where cache-files will be stored
	 * @param string $postfix
	 *        	Termination of file names
	 * @param array $cacheParams
	 *        	defaults to ["fileMode"=>"0755"]
	 */
	public function __construct($root, $postfix = "", $cacheParams = []) {
		parent::__construct($root, $postfix);
		$this->_fileMode = (isset($cacheParams["fileMode"])) ? $cacheParams["fileMode"] : 0755;
		if (! is_dir($root))
			\mkdir($root, $this->_fileMode, true);
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 *
	 * @param string $key
	 *        	cache key
	 * @return boolean true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		return file_exists($this->_getPath($key));
	}

	/**
	 * Caches the given data with the given key.
	 *
	 * @param string $key
	 *        	cache key
	 * @param string $content
	 *        	the source-code to be cached
	 * @param string $tag
	 * @throws CacheException if file could not be written
	 */
	protected function storeContent($key, $content, $tag) {
		$path = $this->_getPath($key);
		$dir = pathinfo($path, PATHINFO_DIRNAME);
		if (UFileSystem::safeMkdir($dir)) {
			$content = \str_replace("<?php", "", $content);
			$content = eval($content);
			$content = "<?php\n" . UArray::asPhpClass($content, $this->getClassname($key), $this->getNamespace($key), true);
			if (@\file_put_contents($path, $content, LOCK_EX) === false) {
				throw new CacheException("Unable to write cache file: {$path}");
			}
			if (@\chmod($path, $this->_fileMode) === false) {
				throw new CacheException("Unable to set permissions of cache file: {$path}");
			}
		} else {
			throw new CacheException("Unable to create folder : {$dir}");
		}
	}

	protected function getCompleteClassname($key) {
		$key = \str_replace([
			'.',
			'/'
		], [
			'_',
			'\\'
		], $key);
		return '\\cache\\' . $key . '_cache';
	}

	protected function getNamespace($key) {
		$key = \str_replace([
			'.',
			'/'
		], [
			'_',
			'\\'
		], $key);
		return 'cache\\' . \substr($key, 0, \strrpos($key, '\\'));
	}

	protected function getClassname($key) {
		$key = \str_replace([
			'.',
			'/'
		], [
			'_',
			'\\'
		], $key);
		return \substr($key, \strrpos($key, '\\') + 1) . '_cache';
	}

	/**
	 * Fetches data stored for the given key.
	 *
	 * @param string $key
	 *        	cache key
	 * @return mixed the cached data
	 */
	public function fetch($key) {
		$class = $this->getCompleteClassname($key);
		return $class::$value;
	}

	/**
	 * return data stored for the given key.
	 *
	 * @param string $key
	 *        	cache key
	 * @return mixed the cached data
	 */
	public function file_get_contents($key) {
		return \file_get_contents($this->_getPath($key));
	}

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key
	 *        	cache key
	 * @return int unix timestamp
	 */
	public function getTimestamp($key) {
		return \filemtime($this->_getPath($key));
	}

	/**
	 * Maps a cache-key to the absolute path of a PHP file
	 *
	 * @param string $key
	 *        	cache key
	 * @return string absolute path of the PHP file
	 */
	private function _getPath($key) {
		$key = \str_replace('.', '_', $key);
		return $this->_root . $key . '_cache.php';
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::remove()
	 */
	public function remove($key) {
		$file = $this->_getPath($key);
		if (\file_exists($file))
			return \unlink($file);
		return false;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::clear()
	 */
	public function clear($matches = "") {
		$files = glob($this->_root . $matches . '*');
		foreach ($files as $file) {
			if (\is_file($file))
				\unlink($file);
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::getCacheFiles()
	 */
	public function getCacheFiles($type) {
		return CacheFile::initFromFiles($this->_root . $type, \ucfirst($type), function ($file) {
			$path = UFileSystem::relativePath(dirname($file), $this->_root);
			$file = \basename($file);
			return $path . \DS . substr($file, 0, strpos($file, $this->postfix . '.php'));
		});
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::clearCache()
	 */
	public function clearCache($type) {
		CacheFile::delete($this->_root . \strtolower($type));
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::getCacheInfo()
	 */
	public function getCacheInfo() {
		$result = parent::getCacheInfo();
		$result .= "\nRoot cache directory is <b>" . UFileSystem::cleanPathname($this->_root) . "</b>.";
		return $result;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::getEntryKey()
	 */
	public function getEntryKey($key) {
		return UFileSystem::cleanFilePathname($this->_getPath($key));
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\cache\system\AbstractDataCache::setRoot()
	 */
	public function setRoot($root) {
		$this->_root = rtrim($root, \DS) . \DS;
	}
}
