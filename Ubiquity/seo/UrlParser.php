<?php

namespace Ubiquity\seo;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UIntrospection;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\controllers\Startup;

class UrlParser {
	public static $frequencies = [ 'always','hourly','daily','weekly','monthly','yearly','never' ];
	private $urls;
	private $config;

	/**
	 *
	 * @return mixed
	 */
	public function getConfig() {
		return $this->config;
	}

	public function __construct() {
		$this->urls = [ ];
		$this->config = Startup::getConfig ();
	}

	public function parse() {
		$routes = CacheManager::getRoutes ();
		foreach ( $routes as $path => $route ) {
			$url = $this->parseUrl ( $path, $route );
			if (isset ( $url )) {
				if (! isset ( $this->urls [$path] ))
					$this->urls [$path] = $url;
			}
		}
	}

	public function parseArray($array, $existing = true) {
		foreach ( $array as $url ) {
			$this->urls [$url ['location']] = Url::fromArray ( $url, $existing );
		}
	}

	protected function parseUrl($path, $route) {
		if (isset ( $route ["controller"] )) {
			$controller = $route ["controller"];
			$action = $route ["action"];
		} elseif (isset ( $route ["get"] )) {
			return $this->parseUrl ( $path, $route ["get"] );
		} else {
			return;
		}
		$lastModified = self::getLastModified ( $controller, $action );
		if ($lastModified !== false) {
			$url = new Url ( $path, $lastModified );
			return $url;
		}
		return;
	}

	public static function getLastModified($controller, $action) {
		if (\class_exists ( $controller )) {
			$classCode = UIntrospection::getClassCode ( $controller );
			$lastModified = UFileSystem::lastModified ( UIntrospection::getFileName ( $controller ) );
			if (\is_array ( $classCode )) {
				$reflexAction = new \ReflectionMethod ( $controller . '::' . $action );
				$views = UIntrospection::getLoadedViews ( $reflexAction, $classCode );
				$baseView=\ROOT.\DS."views".\DS;
				foreach ( $views as $view ) {
					$file = $baseView. $view;
					$viewDate = UFileSystem::lastModified ( $file );
					if ($viewDate > $lastModified)
						$lastModified = $viewDate;
				}
			}
			return $lastModified;
		}
		return false;
	}

	/**
	 *
	 * @return array
	 */
	public function getUrls() {
		return $this->urls;
	}
}

