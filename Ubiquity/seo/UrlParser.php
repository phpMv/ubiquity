<?php

namespace Ubiquity\seo;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UIntrospection;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\controllers\Startup;

class UrlParser {
	public static $frequencies=[ 'always','hourly','daily','weekly','monthly','yearly','never' ];
	private $routes;
	private $config;

	public function __construct() {
		$this->routes=CacheManager::getRoutes();
		$this->config=Startup::getConfig();
	}

	public function parse() {
		$urls=[ ];
		foreach ( $this->routes as $path => $route ) {
			$url=$this->parseUrl($path, $route);
			if (isset($url)) {
				$urls[]=$url;
			}
		}
		return $urls;
	}

	protected function parseUrl($path, $route) {
		if (isset($route["controller"])) {
			$controller=$route["controller"];
			$action=$route["action"];
		} elseif (isset($route["get"])) {
			return $this->parse($route["get"]);
		} else {
			return;
		}
		$url=new Url($path, $this->getLastModified($controller, $action));
		return $url;
	}

	protected function getLastModified($controller, $action) {
		$classCode=UIntrospection::getClassCode($controller);
		$lastModified=UFileSystem::lastModified(UIntrospection::getFileName($controller));
		$reflexAction=new \ReflectionMethod([ $controller,$action ]);
		$actionCode=UIntrospection::getMethodCode($reflexAction, $classCode);
		$views=UIntrospection::getLoadedViews($reflexAction, $actionCode);
		foreach ( $views as $view ) {
			$file=ROOT . DS . "views" . DS . $view;
			$viewDate=UFileSystem::lastModified($file);
			if ($viewDate > $lastModified)
				$lastModified=$viewDate;
		}
		return $lastModified;
	}
}

