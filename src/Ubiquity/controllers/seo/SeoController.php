<?php

namespace Ubiquity\controllers\seo;

use Ubiquity\controllers\Controller;
use Ubiquity\seo\UrlParser;
use Ubiquity\utils\http\UResponse;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\seo\ControllerSeo;

class SeoController extends Controller {
	const SEO_PREFIX = 'seo';
	protected $urlsKey = 'urls';
	protected $seoTemplateFilename = '@framework/Seo/sitemap.xml.html';

	public function index() {
		$config = Startup::getConfig ();
		$base = \rtrim ( $config ['siteUrl'], '/' );
		UResponse::asXml ();
		UResponse::noCache ();
		$urls = $this->_getArrayUrls ();
		if (\is_array ( $urls )) {
			$parser = new UrlParser ();
			$parser->parseArray ( $urls );
			$this->loadView ( $this->seoTemplateFilename, [ 'urls' => $parser->getUrls (),'base' => $base ] );
		}
	}

	public function _refresh() {
	}

	public function getPath() {
		$seo = new ControllerSeo ( \get_class ( $this ) );
		return $seo->getPath ();
	}

	public function _save($array) {
		CacheManager::$cache->store ( $this->_getUrlsFilename (), $array );
	}

	public function _getArrayUrls() {
		$key = $this->_getUrlsFilename ();
		if (! CacheManager::$cache->exists ( $key )) {
			$this->_save ( [ ] );
		}
		return CacheManager::$cache->fetch ( $key );
	}

	/**
	 *
	 * @return string
	 */
	public function _getUrlsFilename() {
		return self::getUrlsFileName ( $this->urlsKey );
	}

	public static function getUrlsFileName($urlsKey) {
		return self::SEO_PREFIX . \DS . $urlsKey;
	}

	/**
	 *
	 * @return string
	 */
	public function _getSeoTemplateFilename() {
		return $this->seoTemplateFilename;
	}
}

