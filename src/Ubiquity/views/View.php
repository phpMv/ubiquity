<?php

namespace Ubiquity\views;

use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\controllers\Startup;

/**
 * Represents a view.
 * Ubiquity\views$View
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.6
 *
 */
class View {
	private $vars;

	public function __construct() {
		$this->vars = [];
	}

	protected function includeFileAsString($filename) {
		\ob_start ();
		include ($filename);
		return \ob_get_clean ();
	}

	public function setVar($name, $value) {
		$this->vars [$name] = $value;
		return $this;
	}

	public function setVars($vars) {
		if (\is_array ( $vars )){
			$this->vars = \array_merge ( $this->vars, $vars );
		} else {
			$this->vars = $vars;
		}
		return $this;
	}

	public function getVar($name) {
		return $this->vars [$name]??null;
	}

	/**
	 * Renders the view $viewName.
	 *
	 * @param string $viewName The view name
	 * @param boolean $asString If true, the view is not displayed but returned as a string (usable in a variable)
	 * @throws \Exception
	 * @return string
	 */
	public function render($viewName, $asString = false) {
		$templateEngine = Startup::$templateEngine;
		$ext = \pathinfo ( $viewName, PATHINFO_EXTENSION );
		if ($ext == null){
			$viewName = $viewName . '.'.Startup::getViewNameFileExtension();
		}
		$data = $this->vars;
		if ($templateEngine instanceof TemplateEngine) {
			return $templateEngine->render ( $viewName, $data, $asString );
		}

		if (\is_array ( $data )) {
			\extract ( $data );
		}
		$fileName = \ROOT . \DS . 'views' . \DS . $viewName;
		if (\file_exists ( $fileName )) {
			if ($asString) {
				return $this->includeFileAsString ( $fileName );
			} else {
				include ($fileName);
			}
		} else {
			throw new \Exception ( "View {$viewName} not found!" );
		}
	}

	public function getBlockNames($templateName) {
		return Startup::$templateEngine->getBlockNames ( $templateName );
	}

	public function getCode($templateName) {
		return Startup::$templateEngine->getCode ( $templateName );
	}
}
