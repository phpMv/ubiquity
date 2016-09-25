<?php
namespace micro\views\engine;

class Twig extends TemplateEngine{
	private $twig;
	public function __construct($options=array()){
		$loader = new \Twig_Loader_Filesystem(ROOT.DS."views/");
		$this->twig = new \Twig_Environment($loader, $options);
	}
	/* (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render($viewName, $pData, $asString) {
		$pData["config"]=$GLOBALS["config"];
		$render=$this->twig->render($viewName,$pData);
		if($asString){
			return $render;
		}else
			echo $render;
	}
}
