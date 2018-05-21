<?php

namespace Ubiquity\views\engine\micro;

use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UFileSystem;

class MicroTemplateEngine extends TemplateEngine {
	private $viewsFolder;

	public function __construct() {
		$this->viewsFolder=ROOT . DS . "views/";
	}

	/*
	 * (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render($viewName, $pData, $asString) {
		$config=Startup::getConfig();
		$fileName=$this->viewsFolder . $viewName;
		if (is_array($pData)) {
			extract($pData);
		}
		$tpl=new TemplateParser($fileName);
		$content=eval('?>' . $tpl->__toString());
		if ($asString)
			return $content;
		else
			echo $content;
	}
	public function getBlockNames($templateName) {
		return [];
	}
	
	public function getCode($templateName){
		$fileName=$this->viewsFolder . $templateName;
		return UFileSystem::load($fileName);
	}

}
