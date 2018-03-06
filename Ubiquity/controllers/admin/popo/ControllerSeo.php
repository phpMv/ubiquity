<?php
namespace Ubiquity\controllers\admin\popo;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\Router;

class ControllerSeo{
	private $name;
	private $urlsFile;
	private $siteMapTemplate;
	private $route;

	public function __construct($className=null){
		if(isset($className) && \class_exists($className)){
			$route=Router::getRouteInfoByControllerAction($className, "index");
			if($route){
				$this->route=$route["path"];
			}
			$ctrl=new $className();
			$this->name=$className;
			$this->urlsFile=$ctrl->_getUrlsFilename();
			$this->siteMapTemplate=$ctrl->_getSeoTemplateFilename();
		}
	}
	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getUrlsFile() {
		return $this->urlsFile;
	}

	/**
	 * @return mixed
	 */
	public function getSiteMapTemplate() {
		return $this->siteMapTemplate;
	}

	/**
	 * @return mixed
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param mixed $urlsFile
	 */
	public function setUrlsFile($urlsFile) {
		$this->urlsFile = $urlsFile;
	}

	/**
	 * @param mixed $siteMapTemplate
	 */
	public function setSiteMapTemplate($siteMapTemplate) {
		$this->siteMapTemplate = $siteMapTemplate;
	}

	/**
	 * @param mixed $route
	 */
	public function setRoute($route) {
		$this->route = $route;
	}

	public static function init(){
		$result=[ ];
		$config=Startup::getConfig();

		$files=CacheManager::getControllersFiles($config, true);
		try {
			$restCtrls=CacheManager::getRestCache();
		} catch ( \Exception $e ) {
			$restCtrls=[ ];
		}

		foreach ( $files as $file ) {
			if (is_file($file)) {
				$controllerClass=ClassUtils::getClassFullNameFromFile($file);
				if (isset($restCtrls[$controllerClass]) === false) {
					$reflect=new \ReflectionClass($controllerClass);
					if (!$reflect->isAbstract() && $reflect->isSubclassOf('Ubiquity\controllers\seo\SeoController')) {
						$result[]=new ControllerSeo($controllerClass);
					}
				}
			}
		}
		return $result;
	}

}