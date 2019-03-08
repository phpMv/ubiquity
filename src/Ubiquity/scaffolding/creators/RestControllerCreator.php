<?php

namespace Ubiquity\scaffolding\creators;

use Ubiquity\scaffolding\ScaffoldController;
use Ubiquity\utils\base\UString;
use Ubiquity\controllers\rest\RestServer;
use Ubiquity\utils\base\UFileSystem;

/**
 * Ubiquity\scaffolding\creators$RestControllerCreator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class RestControllerCreator extends BaseControllerCreator {
	private $resource;

	public function __construct($restControllerName, $resource, $routePath = '') {
		$this->controllerName = $restControllerName;
		$this->routePath = $routePath;
		$this->controllerNS = RestServer::getRestNamespace ();
		$this->resource = $resource;
	}

	public function create(ScaffoldController $scaffoldController, $reInit = null) {
		$this->scaffoldController = $scaffoldController;
		$resource = $this->resource;
		$controllerName = $this->controllerName;
		$controllerNS = $this->controllerNS;
		$messages = [ ];

		$restControllersDir = \ROOT . \DS . str_replace ( "\\", \DS, $controllerNS );
		UFileSystem::safeMkdir ( $restControllersDir );

		$filename = $restControllersDir . \DS . $controllerName . ".php";
		if (! \file_exists ( $filename )) {
			$templateDir = $scaffoldController->getTemplateDir ();
			$namespace = '';
			if ($controllerNS != null)
				$namespace = "namespace " . $controllerNS . ";";
			if ($this->routePath != null) {
				$this->addRoute ( $this->routePath );
			}
			UFileSystem::openReplaceWriteFromTemplateFile ( $templateDir . "restController.tpl", $filename, [ "%resource%" => $resource,"%route%" => $this->routePath,"%controllerName%" => $controllerName,"%namespace%" => $namespace ] );
			$messages [] = $scaffoldController->showSimpleMessage ( "The <b>" . $controllerName . "</b> Rest controller has been created in <b>" . UFileSystem::cleanPathname ( $filename ) . "</b>.", "success", "Rest creation", "checkmark circle", 30000, "msgGlobal" );
			if (isset ( $reInit )) {
				$this->scaffoldController->initRestCache ( false );
			}
		} else {
			$messages [] = $scaffoldController->showSimpleMessage ( "The file <b>" . $filename . "</b> already exists.<br>Can not create the <b>" . $controllerName . "</b> Rest controller!", "warning", "Rest error", "warning circle", 30000, "msgGlobal" );
		}
		$this->scaffoldController->_refreshRest ( true );
		echo implode ( "\n", $messages );
	}

	protected function addViews(&$uses, &$messages, &$classContent) {
	}
}

