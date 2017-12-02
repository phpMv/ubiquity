<?php
namespace micro\controllers\admin\traits;

use Ajax\JsUtils;
use micro\cache\CacheManager;
use micro\controllers\Startup;
use micro\controllers\admin\popo\CacheFile;

/**
 * @author jc
 * @property JsUtils $jquery
 */
trait CacheTrait{
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	abstract public function _getAdminFiles();

	public function setCacheTypes(){
		$config=Startup::getConfig();
		if(isset($_POST["cacheTypes"]))
			$caches=$_POST["cacheTypes"];
		else
			$caches=[];
		$cacheFiles=[];
		foreach ($caches as $cache){
			$cacheFiles=\array_merge($cacheFiles,CacheFile::init(ROOT . DS .CacheManager::getCacheDirectory().$cache, \ucfirst($cache)));
		}
		$dt=$this->_getAdminViewer()->getCacheDataTable($cacheFiles);
		echo $dt->refresh();
		echo $this->jquery->compile($this->view);
	}

	public function deleteCacheFile(){
		if(isset($_POST["toDelete"])){
			$toDelete=$_POST["toDelete"];
			if(\file_exists($toDelete))
				\unlink($toDelete);
		}
		$this->setCacheTypes();
	}

	public function deleteAllCacheFiles(){
		if(isset($_POST["type"])){
			\session_destroy();
			$config=Startup::getConfig();
			$toDelete=$_POST["type"];
			CacheFile::delete(ROOT . DS .CacheManager::getCacheDirectory().\strtolower($toDelete));
		}
		$this->setCacheTypes();
	}

	public function initCacheType(){
		if(isset($_POST["type"])){
			$type=$_POST["type"];
			$config=Startup::getConfig();
			switch ($type){
				case "Models":
					CacheManager::initCache($config,"models");
					break;
				case "Controllers":
					CacheManager::initCache($config,"controllers");
					break;
			}

		}
		$this->setCacheTypes();
	}

	public function _initModelsCache(){
		$config=Startup::getConfig();
		\ob_start();
		CacheManager::initCache($config,"models");
		\ob_end_clean();
		$this->models();
	}
}