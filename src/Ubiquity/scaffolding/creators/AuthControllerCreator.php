<?php

namespace Ubiquity\scaffolding\creators;

use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UString;
use Ubiquity\scaffolding\ScaffoldController;

/**
 * Creates an authentification controller.
 * Ubiquity\scaffolding\creators$AuthControllerCreator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class AuthControllerCreator extends BaseControllerCreator {
	private $baseClass;

	public function __construct($controllerName, $baseClass, $views = null, $routePath = "") {
		parent::__construct ( $controllerName, $routePath, $views );
		$this->baseClass = $baseClass;
	}

	public function create(ScaffoldController $scaffoldController) {
		$this->scaffoldController = $scaffoldController;
		$classContent = "";
		if ($this->baseClass == "\\Ubiquity\\controllers\\auth\\AuthController") {
			$controllerTemplate = "authController.tpl";
			$uses = [ "use Ubiquity\\utils\\http\\USession;","use Ubiquity\\utils\\http\\URequest;" ];
		} else {
			$controllerTemplate = "authController_.tpl";
			$uses = [ ];
		}
		$controllerNS = $this->controllerNS;

		$messages = [ ];
		$routeName = $this->controllerName;
		if (isset ( $this->views )) {
			$this->addViews ( $uses, $messages, $classContent );
		}
		if ($this->routePath != null) {
			if (UString::isNotNull ( $this->routePath )) {
				$routeName = $this->addRoute ( $this->routePath );
			}
		}
		$uses = implode ( "\n", $uses );
		$messages [] = $scaffoldController->_createController ( $this->controllerName, [ "%routeName%" => $routeName,"%route%" => $this->routePath,"%uses%" => $uses,"%namespace%" => $controllerNS,"%baseClass%" => $this->baseClass,"%content%" => $classContent ], $controllerTemplate );
		echo implode ( "\n", $messages );
	}

	protected function addViews(&$uses, &$messages, &$classContent) {
		$scaffoldController = $this->scaffoldController;
		$authControllerName = $this->controllerName;
		$authViews = explode ( ",", $this->views );
		$uses [] = "use controllers\\auth\\files\\{$authControllerName}Files;";
		$uses [] = "use Ubiquity\\controllers\\auth\\AuthFiles;";
		$classContent .= $scaffoldController->_createMethod ( "protected", "getFiles", "", ": AuthFiles", "\t\treturn new {$authControllerName}Files();" );
		$classFilesContent = [ ];
		foreach ( $authViews as $file ) {
			if (isset ( ScaffoldController::$views ["auth"] [$file] )) {
				$frameworkViewname = ScaffoldController::$views ["auth"] [$file];
				$scaffoldController->createAuthCrudView ( $frameworkViewname, $authControllerName, $file );
				$classFilesContent [] = $scaffoldController->_createMethod ( "public", "getView" . ucfirst ( $file ), "", "", "\t\treturn \"" . $authControllerName . "/" . $file . ".html\";" );
			}
		}
		$messages [] = $this->createAuthFilesClass ( $scaffoldController, implode ( "", $classFilesContent ) );
	}

	protected function createAuthFilesClass(ScaffoldController $scaffoldController, $classContent = "") {
		$ns = Startup::getNS ( "controllers" ) . "auth\\files";
		$uses = "\nuse Ubiquity\\controllers\\auth\\AuthFiles;";
		return $scaffoldController->_createClass ( "class.tpl", $this->controllerName . "Files", $ns, $uses, "extends AuthFiles", $classContent );
	}
}

