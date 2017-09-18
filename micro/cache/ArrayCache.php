<?php

/**
 * Inspired by (c) Rasmus Schultz <rasmus@mindplay.dk>
 * <https://github.com/mindplay-dk/php-annotations>
 */
namespace micro\cache;

/**
 * This class is responsible for storing Arrays in PHP files.
 */
class ArrayCache {
	/**
	 *
	 * @var string The PHP opening tag (used when writing cache files)
	 */
	const PHP_TAG="<?php\n";

	/**
	 *
	 * @var int The file mode used when creating new cache files
	 */
	private $_fileMode;

	/**
	 *
	 * @var string Absolute path to a folder where cache files will be created
	 */
	private $_root;

	/**
	 *
	 * @var string Termination of file names
	 */
	private $postfix;

	/**
	 * Initializes the file cache-provider
	 * @param string $root absolute path to the root-folder where cache-files will be stored
	 * @param string Termination of file names
	 * @param int $fileMode file creation mode; defaults to 0777
	 */
	public function __construct($root, $postfix="", $fileMode=0777) {
		$this->_root=$root;
		$this->_fileMode=$fileMode;
		$this->postfix=$postfix;
		if (!is_dir($root))
			\mkdir($root, $fileMode, true);
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 * @param string $key cache key
	 * @return bool true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		return file_exists($this->_getPath($key));
	}

	public function expired($key, $duration) {
		if ($this->exists($key)) {
			if (\is_int($duration)) {
				return \time() - $this->getTimestamp($key) > $duration;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * Caches the given data with the given key.
	 * @param string $key cache key
	 * @param string $code the source-code to be cached
	 * @throws AnnotationException if file could not be written
	 */
	public function store($key, $code, $php=true) {
		$path=$this->_getPath($key);
		$content="";
		if ($php)
			$content=self::PHP_TAG;
		$content.=$code . "\n";
		if (@\file_put_contents($path, $content, LOCK_EX) === false) {
			throw new \Exception("Unable to write cache file: {$path}");
		}
		if (@\chmod($path, $this->_fileMode) === false) {
			throw new \Exception("Unable to set permissions of cache file: {$path}");
		}
	}

	/**
	 * Fetches data stored for the given key.
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function fetch($key) {
		return include ($this->_getPath($key));
	}

	/**
	 * return data stored for the given key.
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function file_get_contents($key) {
		return \file_get_contents($this->_getPath($key));
	}

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key cache key
	 * @return int unix timestamp
	 */
	public function getTimestamp($key) {
		return \filemtime($this->_getPath($key));
	}

	/**
	 * Maps a cache-key to the absolute path of a PHP file
	 *
	 * @param string $key cache key
	 * @return string absolute path of the PHP file
	 */
	private function _getPath($key) {
		return $this->_root . DIRECTORY_SEPARATOR . $key . $this->postfix . '.php';
	}

	public function remove($key) {
		$file=$this->_getPath($key);
		if (\file_exists($file))
			\unlink($file);
	}

	public function clear() {
		$files=glob($this->_root . '/*');
		foreach ( $files as $file ) {
			if (\is_file($file))
				\unlink($file);
		}
	}

	/**
	 *
	 * @return string absolute path of the folder where cache files are created
	 */
	public function getRoot() {
		return $this->_root;
	}
}
