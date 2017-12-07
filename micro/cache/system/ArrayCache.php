<?php
namespace micro\cache\system;

use micro\controllers\admin\popo\CacheFile;
use micro\cache\CacheManager;

/**
 * This class is responsible for storing Arrays in PHP files.
 */
class ArrayCache extends AbstractDataCache{
	/**
	 *
	 * @var int The file mode used when creating new cache files
	 */
	private $_fileMode;

	/**
	 * Initializes the file cache-provider
	 * @param string $root absolute path to the root-folder where cache-files will be stored
	 * @param string Termination of file names
	 * @param array $params fileMode file creation mode; defaults to 0777
	 */
	public function __construct($root, $postfix="", $params=['fileMode'=>0777]) {
		parent::__construct($root,$postfix);
		if(!isset($params['fileMode']))
			$params['fileMode']=0777;
		$this->_fileMode=$params['fileMode'];
		if (!is_dir($root))
			\mkdir($root, $params['fileMode'], true);
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 * @param string $key cache key
	 * @return boolean true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		return file_exists($this->_getPath($key));
	}

	/**
	 * Caches the given data with the given key.
	 * @param string $key cache key
	 * @param string $content the source-code to be cached
	 * @throws AnnotationException if file could not be written
	 */
	protected function storeContent($key,$content,$tag) {
		$path=$this->_getPath($key);
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

	public function getCacheFiles($type){
		return CacheFile::initFromFiles(ROOT . DS .CacheManager::getCacheDirectory().$type, \ucfirst($type),function($file) use($type){$file=\basename($file);return $type."/".substr($file, 0, strpos($file, $this->postfix.'.php'));});
	}

	public function clearCache($type){
		CacheFile::delete(ROOT . DS .CacheManager::getCacheDirectory().\strtolower($type));
	}

	public function getCacheInfo(){
		$result=parent::getCacheInfo();
		$result.="<br>Root cache directory is <b>".$this->_root."</b>.";
		return $result;
	}

	public function getEntryKey($key){
		return $this->_getPath($key);
	}
}
