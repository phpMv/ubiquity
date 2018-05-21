<?php

namespace Ubiquity\views;

use Ubiquity\utils\base\UString;
use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\controllers\Startup;

/**
 * Represents a view
 * @author jc
 * @version 1.0.2
 */
class View {
	private $vars;

	public function __construct() {
		$this->vars=array ();
	}

	protected function includeFileAsString($filename) {
		\ob_start();
		include ($filename);
		return \ob_get_clean();
	}

	public function setVar($name, $value) {
		$this->vars[$name]=$value;
		return $this;
	}

	public function setVars($vars) {
		if (\is_array($vars))
			$this->vars=\array_merge($this->vars, $vars);
		else
			$this->vars=$vars;
		return $this;
	}

	public function getVar($name) {
		if (\array_key_exists($name, $this->vars)) {
			return $this->vars[$name];
		}
	}

	/**
	 * affiche la vue $viewName
	 * @param string $viewName nom de la vue à charger
	 * @param boolean $asString Si vrai, la vue n'est pas affichée mais retournée sous forme de chaîne (utilisable dans une variable)
	 * @throws \Exception
	 * @return string
	 */
	public function render($viewName, $asString=false) {
		$config=Startup::getConfig();
		$ext=\pathinfo($viewName, PATHINFO_EXTENSION);
		if ($ext === null)
			$viewName=$viewName . ".php";
		$data=$this->vars;
		if (!UString::endswith($viewName, ".php") && @$config["templateEngine"] instanceof TemplateEngine) {
			return $config["templateEngine"]->render($viewName, $data, $asString);
		}

		if (is_array($data)) {
			extract($data);
		}
		$fileName=ROOT . DS . "views/" . $viewName;
		if(file_exists($fileName)){
			if ($asString) {
				return $this->includeFileAsString($fileName);
			} else {
				include ($fileName);
			}
		}else{
			throw new \Exception("View {$viewName} not found!");
		}
	}
	
	public function getBlockNames($templateName){
		$config=Startup::getConfig();
		return $config["templateEngine"]->getBlockNames($templateName);
	}
	
	public function getCode($templateName){
		$config=Startup::getConfig();
		return $config["templateEngine"]->getCode($templateName);
	}
}
