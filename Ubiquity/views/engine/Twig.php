<?php

namespace Ubiquity\views\engine;

use Ubiquity\controllers\Startup;
use Ubiquity\controllers\Router;
use Ubiquity\cache\CacheManager;
use Ubiquity\core\Framework;
use Ubiquity\utils\base\UFileSystem;

class Twig extends TemplateEngine {
	private $twig;

	public function __construct($options=array()) {
		$loader=new \Twig_Loader_Filesystem(ROOT . DS . "views/");
		$loader->addPath(implode(DS,[Startup::getFrameworkDir(),"..","core","views"]).DS,"framework");
		if(isset($options["cache"]) && $options["cache"]===true)
			$options["cache"]=ROOT.DS.CacheManager::getCacheDirectory().DS."views/";
		$this->twig=new \Twig_Environment($loader, $options);

		$function=new \Twig_SimpleFunction('path', function ($name,$params=[],$absolute=false) {
			return Router::path($name,$params,$absolute);
		});
		$this->twig->addFunction($function);
		$function=new \Twig_SimpleFunction('url', function ($name,$params) {
			return Router::url($name,$params);
		});
		$this->twig->addFunction($function);
		
		$test=new \Twig_SimpleTest('instanceOf', function($var,$class){ 
			return  $var instanceof $class;
		});
		$this->twig->addTest($test);
		
		$this->twig->addGlobal("app", new Framework());
	}

	/*
	 * (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render($viewName, $pData, $asString) {
		$pData["config"]=Startup::getConfig();
		$render=$this->twig->render($viewName, $pData);
		if ($asString) {
			return $render;
		} else
			echo $render;
	}

	public function getBlockNames($templateName) {
		return $this->twig->load($templateName)->getBlockNames();
	}
	
	public function getCode($templateName){
		return UFileSystem::load($this->twig->load($templateName)->getSourceContext()->getPath());
	}

}
