<?php

namespace Ubiquity\views\engine\micro;

use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\utils\base\UFileSystem;

class MicroTemplateEngine extends TemplateEngine {
	private $viewsFolder;
	private $parsers = [ ];

	private function getTemplateParser(string $viewName): TemplateParser {
		if (! isset ( $this->parsers [$viewName] )) {
			$this->parsers [$viewName] = new TemplateParser ( $this->viewsFolder . $viewName );
		}
		return $this->parsers [$viewName];
	}

	public function __construct() {
		$this->viewsFolder = \ROOT . \DS . 'views' . \DS;
	}

	/*
	 * (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render($viewName, $pData, $asString) {
		if (\is_array ( $pData )) {
			\extract ( $pData );
		}
		$content = eval ( '?>' . $this->getTemplateParser ( $viewName )->__toString () );
		if ($asString)
			return $content;
		else
			echo $content;
	}

	public function getBlockNames($templateName) {
		return [ ];
	}

	public function getCode($templateName) {
		$fileName = $this->viewsFolder . $templateName;
		return UFileSystem::load ( $fileName );
	}

	public function exists($name) {
		$filename = $this->viewsFolder . $name;
		return \file_exists ( $filename );
	}
}
