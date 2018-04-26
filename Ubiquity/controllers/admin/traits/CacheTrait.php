<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\admin\popo\CacheFile;
use Ubiquity\utils\http\URequest;
use Ajax\semantic\html\collections\form\HtmlForm;

/**
 *
 * @author jc
 * @property \Ajax\JsUtils\JsUtils $jquery
 */
trait CacheTrait{

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getAdminFiles();

	public function setCacheTypes() {
		if (isset ( $_POST ["cacheTypes"] ))
			$caches = $_POST ["cacheTypes"];
		else
			$caches = [ ];
		$cacheFiles = [ ];
		foreach ( $caches as $cache ) {
			if ($cache == 'models' || $cache == 'controllers') {
				$cacheFiles = \array_merge ( $cacheFiles, CacheManager::$cache->getCacheFiles ( $cache ) );
			} else {
				$cacheFiles = \array_merge ( $cacheFiles, CacheFile::initFromFiles ( ROOT . DS . CacheManager::getCacheDirectory () . $cache, \ucfirst ( $cache ) ) );
			}
		}
		$dt = $this->_getAdminViewer ()->getCacheDataTable ( $cacheFiles );
		echo $dt->refresh ();
		echo $this->jquery->compile ( $this->view );
	}

	public function deleteCacheFile() {
		if (isset ( $_POST ["toDelete"] )) {
			$toDelete = $_POST ["toDelete"];
			$type = \strtolower ( $_POST ["type"] );
			if ($type == 'models' || $type == 'controllers') {
				CacheManager::$cache->remove ( $toDelete );
			} else {
				if (\file_exists ( $toDelete ))
					\unlink ( $toDelete );
			}
		}
		$this->setCacheTypes ();
	}

	public function deleteAllCacheFiles() {
		if (isset ( $_POST ["type"] )) {
			\session_destroy ();
			$toDelete = \strtolower ( $_POST ["type"] );
			if ($toDelete == 'models' || $toDelete == 'controllers') {
				CacheManager::$cache->clearCache ( $toDelete );
			} else {
				CacheFile::delete ( ROOT . DS . CacheManager::getCacheDirectory () . \strtolower ( $toDelete ) );
			}
		}
		$this->setCacheTypes ();
	}

	public function _showFileContent() {
		if (URequest::isPost ()) {
			$type = \strtolower ( $_POST ["type"] );
			$filename = $_POST ["filename"];
			$key = $_POST ["key"];
			if ($type == 'models' || $type == 'controllers') {
				$content = CacheManager::$cache->file_get_contents ( $key );
			} else {
				if (\file_exists ( $filename )) {
					$content = \file_get_contents ( $filename );
				}
			}
			$modal = $this->jquery->semantic ()->htmlModal ( "file", $type . " : " . \basename ( $filename ) );
			$frm = new HtmlForm ( "frmShowFileContent" );
			$frm->addTextarea ( "file-content", null, $content, "", 10 );
			$modal->setContent ( $frm );
			$modal->addAction ( "Close" );
			$this->jquery->exec ( "$('#file').modal('show');", true );
			echo $modal;
			echo $this->jquery->compile ( $this->view );
		}
	}

	public function initCacheType() {
		if (isset ( $_POST ["type"] )) {
			$type = $_POST ["type"];
			$config = Startup::getConfig ();
			switch ($type) {
				case "Models" :
					CacheManager::initCache ( $config, "models" );
					break;
				case "Controllers" :
					CacheManager::initCache ( $config, "controllers" );
					break;
			}
		}
		$this->setCacheTypes ();
	}

	public function _initModelsCache() {
		$config = Startup::getConfig ();
		\ob_start ();
		CacheManager::initCache ( $config, "models" );
		\ob_end_clean ();
		$this->models ();
	}
}
