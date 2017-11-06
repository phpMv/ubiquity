<?php

namespace micro\views\engine;

use micro\controllers\Startup;
use micro\controllers\Router;

class Twig extends TemplateEngine {
	private $twig;

	public function __construct($options=array()) {
		$loader=new \Twig_Loader_Filesystem(ROOT . DS . "views/");
		if(isset($options["cache"]) && $options["cache"]===true)
			$options["cache"]=ROOT.DS.Startup::getConfig()["cacheDirectory"].DS."views/";
		$this->twig=new \Twig_Environment($loader, $options);

		$function=new \Twig_SimpleFunction('getRouteByName', function ($name) {
			return Router::getRouteByName($name);
		});
		$this->twig->addFunction($function);
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
}
