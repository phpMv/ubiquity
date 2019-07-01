<?php

namespace Ubiquity\seo;

use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\Router;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\base\UString;

/**
 * Base class for SEO controllers
 * Ubiquity\seo$ControllerSeo
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class ControllerSeo {
	private $name;
	private $urlsFile;
	private $siteMapTemplate;
	private $route;
	private $inRobots;

	public function __construct($className = null) {
		if (isset ( $className ) && \class_exists ( $className )) {
			$route = Router::getRouteInfoByControllerAction ( $className, "index" );
			if ($route) {
				$this->route = $route ["path"];
			}
			$ctrl = new $className ();
			$this->name = $className;
			$this->urlsFile = $ctrl->_getUrlsFilename ();
			$this->siteMapTemplate = $ctrl->_getSeoTemplateFilename ();
		}
	}

	/**
	 *
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getUrlsFile() {
		return $this->urlsFile;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getSiteMapTemplate() {
		return $this->siteMapTemplate;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 *
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 *
	 * @param mixed $urlsFile
	 */
	public function setUrlsFile($urlsFile) {
		$this->urlsFile = $urlsFile;
	}

	/**
	 *
	 * @param mixed $siteMapTemplate
	 */
	public function setSiteMapTemplate($siteMapTemplate) {
		$this->siteMapTemplate = $siteMapTemplate;
	}

	/**
	 *
	 * @param mixed $route
	 */
	public function setRoute($route) {
		$this->route = $route;
	}

	public function getPath() {
		if (UString::isNotNull ( $this->route ))
			return $this->route;
		$parts = \explode ( "\\", $this->name );
		return end ( $parts );
	}

	public function urlExists() {
		return CacheManager::$cache->exists ( $this->urlsFile );
	}

	public static function init() {
		$result = [ ];
		$config = Startup::getConfig ();

		$robotsContent = "";
		$robotsFile = Startup::getApplicationDir () . \DS . 'robots.txt';
		if (\file_exists ( $robotsFile )) {
			$robotsContent = UFileSystem::load ( $robotsFile );
		}
		$files = CacheManager::getControllersFiles ( $config, true );
		try {
			$restCtrls = CacheManager::getRestCache ();
		} catch ( \Exception $e ) {
			$restCtrls = [ ];
		}

		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$controllerClass = ClassUtils::getClassFullNameFromFile ( $file );
				if (isset ( $restCtrls [$controllerClass] ) === false) {
					if (\class_exists ( $controllerClass, true )) {
						$reflect = new \ReflectionClass ( $controllerClass );
						if (! $reflect->isAbstract () && $reflect->isSubclassOf ( 'Ubiquity\controllers\seo\SeoController' )) {
							$ctrlSeo = new ControllerSeo ( $controllerClass );
							$path = $ctrlSeo->getPath ();
							$ctrlSeo->setInRobots ( $robotsContent !== false && (\strpos ( $robotsContent, $path ) !== false) );
							$result [] = $ctrlSeo;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getInRobots() {
		return $this->inRobots;
	}

	/**
	 *
	 * @param mixed $inRobots
	 */
	public function setInRobots($inRobots) {
		$this->inRobots = $inRobots;
	}
}
