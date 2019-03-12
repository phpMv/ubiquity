<?php

namespace Ubiquity\scaffolding\creators;

use Ubiquity\utils\base\UString;
use Ubiquity\scaffolding\ScaffoldController;
use Ubiquity\controllers\Startup;

/**
 * Base class for class creation in scaffolding.
 * Ubiquity\scaffolding\creators$BaseControllerCreator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
abstract class BaseControllerCreator {
	protected $controllerName;
	protected $routePath;
	protected $views;
	protected $controllerNS;
	protected $templateName;

	/**
	 *
	 * @var ScaffoldController
	 */
	protected $scaffoldController;

	public function __construct($controllerName, $routePath, $views) {
		$this->controllerName = $controllerName;
		$this->routePath = $routePath;
		$this->views = $views;
		$this->controllerNS = Startup::getNS ( "controllers" );
	}

	protected function addRoute(&$routePath) {
		if (! UString::startswith ( $routePath, "/" )) {
			$routePath = "/" . $routePath;
		}
		$routeName = $routePath;
		$routePath = "\n * @route(\"{$routePath}\",\"inherited\"=>true,\"automated\"=>true)";
		return $routeName;
	}

	abstract public function create(ScaffoldController $scaffoldController);

	abstract protected function addViews(&$uses, &$messages, &$classContent);

	/**
	 *
	 * @return mixed
	 */
	public function getTemplateName() {
		return $this->templateName;
	}

	/**
	 *
	 * @param mixed $templateName
	 */
	public function setTemplateName($templateName) {
		$this->templateName = $templateName;
	}
}

