<?php

namespace Ubiquity\scaffolding;

use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\base\UString;
use Ubiquity\controllers\admin\utils\CodeUtils;
use Ubiquity\utils\base\UIntrospection;
use Ubiquity\cache\ClassUtils;

abstract class ScaffoldController {
	public static $views = [ "CRUD" => [ "index" => "@framework/crud/index.html","form" => "@framework/crud/form.html","display" => "@framework/crud/display.html" ],
			"auth" => [ "index" => "@framework/auth/index.html","info" => "@framework/auth/info.html","noAccess" => "@framework/auth/noAccess.html","disconnected" => "@framework/auth/disconnected.html","message" => "@framework/auth/message.html","baseTemplate" => "@framework/auth/baseTemplate.html" ] ];

	protected abstract function getTemplateDir();

	protected abstract function storeControllerNameInSession($controller);

	protected abstract function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null);

	protected abstract function _addMessageForRouteCreation($path, $jsCallback = "");

	public function _createMethod($access, $name, $parameters = "", $return = "", $content = "", $comment = "") {
		$templateDir = $this->getTemplateDir ();
		$keyAndValues = [ "%access%" => $access,"%name%" => $name,"%parameters%" => $parameters,"%content%" => $content,"%comment%" => $comment,"%return%" => $return ];
		return UFileSystem::openReplaceInTemplateFile ( $templateDir . "method.tpl", $keyAndValues );
	}

	public function _createController($controllerName, $variables = [], $ctrlTemplate = 'controller.tpl', $hasView = false, $jsCallback = "") {
		$message = "";
		$templateDir = $this->getTemplateDir ();
		$controllersNS = \rtrim ( Startup::getNS ( "controllers" ), "\\" );
		$controllersDir = \ROOT . \DS . str_replace ( "\\", \DS, $controllersNS );
		$controllerName = \ucfirst ( $controllerName );
		$filename = $controllersDir . \DS . $controllerName . ".php";
		if (\file_exists ( $filename ) === false) {
			$namespace = "";
			if ($controllersNS !== "") {
				$namespace = "namespace " . $controllersNS . ";";
			}
			$msgView = "";
			$indexContent = "";
			if ($hasView) {
				$viewDir = \ROOT . \DS . "views" . \DS . $controllerName . \DS;
				UFileSystem::safeMkdir ( $viewDir );
				$viewName = $viewDir . \DS . "index.html";
				UFileSystem::openReplaceWriteFromTemplateFile ( $templateDir . "view.tpl", $viewName, [ "%controllerName%" => $controllerName,"%actionName%" => "index" ] );
				$msgView = "<br>The default view associated has been created in <b>" . UFileSystem::cleanPathname ( \ROOT . \DS . $viewDir ) . "</b>";
				$indexContent = "\$this->loadView(\"" . $controllerName . "/index.html\");";
			}
			$variables = \array_merge ( $variables, [ "%controllerName%" => $controllerName,"%indexContent%" => $indexContent,"%namespace%" => $namespace ] );
			UFileSystem::openReplaceWriteFromTemplateFile ( $templateDir . $ctrlTemplate, $filename, $variables );
			$msgContent = "The <b>" . $controllerName . "</b> controller has been created in <b>" . UFileSystem::cleanFilePathname ( $filename ) . "</b>." . $msgView;
			if (isset ( $variables ["%path%"] ) && $variables ["%path%"] !== "") {
				$msgContent .= $this->_addMessageForRouteCreation ( $variables ["%path%"], $jsCallback );
			}
			$this->storeControllerNameInSession ( $controllersNS . "\\" . $controllerName );
			$message = $this->showSimpleMessage ( $msgContent, "success", null, "checkmark circle", NULL, "msgGlobal" );
		} else {
			$message = $this->showSimpleMessage ( "The file <b>" . $filename . "</b> already exists.<br>Can not create the <b>" . $controllerName . "</b> controller!", "warning", null, "warning circle", 100000, "msgGlobal" );
		}
		return $message;
	}

	public function addCrudController($crudControllerName, $resource, $crudDatas = null, $crudViewer = null, $crudEvents = null, $crudViews = null, $routePath = '') {
		$classContent = "";
		$uses = [ ];
		$controllerNS = Startup::getNS ( "controllers" );
		$messages = [ ];
		$routeName = $crudControllerName;
		$this->_createMethod ( "public", "__construct", "", "", "\n\t\tparent::__construct();\n\$this->model=\"{$resource}\";" );

		if (isset ( $crudDatas )) {
			$uses [] = "use controllers\\crud\\datas\\{$crudControllerName}Datas;";
			$uses [] = "use Ubiquity\\controllers\\crud\\CRUDDatas;";

			$classContent .= $this->_createMethod ( "protected", "getAdminData", "", ": CRUDDatas", "\t\treturn new {$crudControllerName}Datas(\$this);" );
			$messages [] = $this->createCRUDDatasClass ( $crudControllerName );
		}

		if (isset ( $crudViewer )) {
			$uses [] = "use controllers\\crud\\viewers\\{$crudControllerName}Viewer;";
			$uses [] = "use Ubiquity\\controllers\\admin\\viewers\\ModelViewer;";

			$classContent .= $this->_createMethod ( "protected", "getModelViewer", "", ": ModelViewer", "\t\treturn new {$crudControllerName}Viewer(\$this);" );
			$messages [] = $this->createModelViewerClass ( $crudControllerName );
		}
		if (isset ( $crudEvents )) {
			$uses [] = "use controllers\\crud\\events\\{$crudControllerName}Events;";
			$uses [] = "use Ubiquity\\controllers\\crud\\CRUDEvents;";

			$classContent .= $this->_createMethod ( "protected", "getEvents", "", ": CRUDEvents", "\t\treturn new {$crudControllerName}Events(\$this);" );
			$messages [] = $this->createEventsClass ( $crudControllerName );
		}

		if (isset ( $crudViews )) {
			$crudViews = explode ( ",", $crudViews );
			$uses [] = "use controllers\\crud\\files\\{$crudControllerName}Files;";
			$uses [] = "use Ubiquity\\controllers\\crud\\CRUDFiles;";
			$classContent .= $this->_createMethod ( "protected", "getFiles", "", ": CRUDFiles", "\t\treturn new {$crudControllerName}Files();" );
			$classFilesContent = [ ];
			foreach ( $crudViews as $file ) {
				if (isset ( self::$views ["CRUD"] [$file] )) {
					$frameworkViewname = self::$views ["CRUD"] [$file];
					$this->createCrudView ( $frameworkViewname, $crudControllerName, $file );
					$classFilesContent [] = $this->_createMethod ( "public", "getView" . ucfirst ( $file ), "", "", "\t\treturn \"" . $crudControllerName . "/" . $file . ".html\";" );
				}
			}
			$messages [] = $this->createCRUDFilesClass ( $crudControllerName, implode ( "", $classFilesContent ) );
		}
		if ($routePath != null) {
			if (UString::isNotNull ( $routePath )) {
				if (! UString::startswith ( $routePath, "/" )) {
					$routePath = "/" . $routePath;
				}
				$routeName = $routePath;
				$routePath = "\n * @route(\"{$routePath}\",\"inherited\"=>true,\"automated\"=>true)";
			}
		}
		$uses = implode ( "\n", $uses );
		$messages [] = $this->_createController ( $crudControllerName, [ "%routeName%" => $routeName,"%route%" => $routePath,"%resource%" => $resource,"%uses%" => $uses,"%namespace%" => $controllerNS,"%baseClass%" => "\\Ubiquity\\controllers\\crud\\CRUDController","%content%" => $classContent ], "crudController.tpl" );
		echo implode ( "\n", $messages );
	}

	public function addAuthController($authControllerName, $baseClass, $authViews = null, $routePath = "") {
		$classContent = "";
		if ($baseClass == "\\Ubiquity\\controllers\\auth\\AuthController") {
			$controllerTemplate = "authController.tpl";
			$uses = [ "use Ubiquity\\utils\\http\\USession;","use Ubiquity\\utils\\http\\URequest;" ];
		} else {
			$controllerTemplate = "authController_.tpl";
			$uses = [ ];
		}
		$controllerNS = Startup::getNS ( "controllers" );

		$messages = [ ];
		$routeName = $authControllerName;
		if (isset ( $authViews )) {
			$authViews = explode ( ",", $authViews );
			$uses [] = "use controllers\\auth\\files\\{$authControllerName}Files;";
			$uses [] = "use Ubiquity\\controllers\\auth\\AuthFiles;";
			$classContent .= $this->_createMethod ( "protected", "getFiles", "", ": AuthFiles", "\t\treturn new {$authControllerName}Files();" );
			$classFilesContent = [ ];
			foreach ( $authViews as $file ) {
				if (isset ( self::$views ["auth"] [$file] )) {
					$frameworkViewname = self::$views ["auth"] [$file];
					$this->createCrudView ( $frameworkViewname, $authControllerName, $file );
					$classFilesContent [] = $this->_createMethod ( "public", "getView" . ucfirst ( $file ), "", "", "\t\treturn \"" . $authControllerName . "/" . $file . ".html\";" );
				}
			}
			$messages [] = $this->createAuthFilesClass ( $authControllerName, implode ( "", $classFilesContent ) );
		}
		if ($routePath != null) {
			if (UString::isNotNull ( $routePath )) {
				if (! UString::startswith ( $routePath, "/" )) {
					$routePath = "/" . $routePath;
				}
				$routeName = $routePath;
				$routePath = "\n * @route(\"{$routePath}\",\"inherited\"=>true,\"automated\"=>true)";
			}
		}
		$uses = implode ( "\n", $uses );
		$messages [] = $this->_createController ( $authControllerName, [ "%routeName%" => $routeName,"%route%" => $routePath,"%uses%" => $uses,"%namespace%" => $controllerNS,"%baseClass%" => $baseClass,"%content%" => $classContent ], $controllerTemplate );
		echo implode ( "\n", $messages );
	}

	public function _createClass($template, $classname, $namespace, $uses, $extendsOrImplements, $classContent) {
		$namespaceVar = "";
		if (UString::isNotNull ( $namespace )) {
			$namespaceVar = "namespace {$namespace};";
		}
		$variables = [ "%classname%" => $classname,"%namespace%" => $namespaceVar,"%uses%" => $uses,"%extendsOrImplements%" => $extendsOrImplements,"%classContent%" => $classContent ];
		$templateDir = $this->getTemplateDir ();
		$directory = UFileSystem::getDirFromNamespace ( $namespace );
		UFileSystem::safeMkdir ( $directory );
		$filename = UFileSystem::cleanFilePathname ( $directory . \DS . $classname . ".php" );
		if (! file_exists ( $filename )) {
			UFileSystem::openReplaceWriteFromTemplateFile ( $templateDir . $template, $filename, $variables );
			$message = $this->showSimpleMessage ( "The <b>" . $classname . "</b> class has been created in <b>" . $filename . "</b>.", "success", "Creation", "checkmark circle" );
		} else {
			$message = $this->showSimpleMessage ( "The file <b>" . $filename . "</b> already exists.<br>Can not create the <b>" . $classname . "</b> class!", "warning", "Creation", "warning circle" );
		}
		return $message;
	}

	public function _newAction($controller, $action, $parameters = null, $content = '', $routeInfo = null, $createView = false) {
		$templateDir = $this->getTemplateDir ();
		$msgContent = "";
		$r = new \ReflectionClass ( $controller );
		$ctrlFilename = $r->getFileName ();
		$content = CodeUtils::indent ( $content, 2 );
		$fileContent = \implode ( "", UIntrospection::getClassCode ( $controller ) );
		$fileContent = \trim ( $fileContent );
		$posLast = \strrpos ( $fileContent, "}" );
		if ($posLast !== false) {
			if ($createView) {
				$viewname = $this->_createViewOp ( ClassUtils::getClassSimpleName ( $controller ), $action );
				$content .= "\n\t\t\$this->loadView('" . $viewname . "');\n";
				$msgContent .= "<br>Created view : <b>" . $viewname . "</b>";
			}
			$routeAnnotation = "";
			if (is_array ( $routeInfo )) {
				$name = "route";
				$path = $routeInfo ["path"];
				$routeProperties = [ '"' . $path . '"' ];
				$methods = $routeInfo ["methods"];
				if (UString::isNotNull ( $methods )) {
					$routeProperties [] = '"methods"=>' . $this->getMethods ( $methods );
				}
				if (isset ( $routeInfo ["ck-Cache"] )) {
					$routeProperties [] = '"cache"=>true';
					if (isset ( $routeInfo ["duration"] )) {
						$duration = $routeInfo ["duration"];
						if (\ctype_digit ( $duration )) {
							$routeProperties [] = '"duration"=>' . $duration;
						}
					}
				}
				$routeProperties = \implode ( ",", $routeProperties );
				$routeAnnotation = UFileSystem::openReplaceInTemplateFile ( $templateDir . "annotation.tpl", [ "%name%" => $name,"%properties%" => $routeProperties ] );

				$msgContent .= $this->_addMessageForRouteCreation ( $path );
			}
			$parameters = CodeUtils::cleanParameters ( $parameters );
			$actionContent = UFileSystem::openReplaceInTemplateFile ( $templateDir . "action.tpl", [ "%route%" => "\n" . $routeAnnotation,"%actionName%" => $action,"%parameters%" => $parameters,"%content%" => $content ] );
			$fileContent = \substr_replace ( $fileContent, "\n%content%", $posLast - 1, 0 );
			if (! CodeUtils::isValidCode ( '<?php ' . $content )) {
				echo $this->showSimpleMessage ( "Errors parsing action content!", "warning", "Creation", "warning circle", null, "msgControllers" );
				return;
			} else {
				if (UFileSystem::replaceWriteFromContent ( $fileContent . "\n", $ctrlFilename, [ '%content%' => $actionContent ] )) {
					$msgContent = "The action <b>{$action}</b> is created in controller <b>{$controller}</b>" . $msgContent;
					echo $this->showSimpleMessage ( $msgContent, "info", "Creation", "info circle", null, "msgControllers" );
				}
			}
		}
	}

	protected function getMethods($strMethods) {
		$methods = \explode ( ",", $strMethods );
		$result = [ ];
		foreach ( $methods as $method ) {
			$result [] = '"' . $method . '"';
		}
		return "[" . \implode ( ",", $result ) . "]";
	}

	protected function _createViewOp($controller, $action) {
		$viewName = $controller . "/" . $action . ".html";
		UFileSystem::safeMkdir ( \ROOT . \DS . "views" . \DS . $controller );
		$templateDir = $this->getTemplateDir ();
		UFileSystem::openReplaceWriteFromTemplateFile ( $templateDir . "view.tpl", \ROOT . \DS . "views" . \DS . $viewName, [ "%controllerName%" => $controller,"%actionName%" => $action ] );
		return $viewName;
	}

	protected function createCRUDDatasClass($crudControllerName) {
		$ns = Startup::getNS ( "controllers" ) . "crud\\datas";
		$uses = "\nuse Ubiquity\\controllers\\crud\\CRUDDatas;";
		return $this->_createClass ( "class.tpl", $crudControllerName . "Datas", $ns, $uses, "extends CRUDDatas", "\t//use override/implement Methods" );
	}

	protected function createModelViewerClass($crudControllerName) {
		$ns = Startup::getNS ( "controllers" ) . "crud\\viewers";
		$uses = "\nuse Ubiquity\\controllers\\admin\\viewers\\ModelViewer;";
		return $this->_createClass ( "class.tpl", $crudControllerName . "Viewer", $ns, $uses, "extends ModelViewer", "\t//use override/implement Methods" );
	}

	protected function createEventsClass($crudControllerName) {
		$ns = Startup::getNS ( "controllers" ) . "crud\\events";
		$uses = "\nuse Ubiquity\\controllers\\crud\\CRUDEvents;";
		return $this->_createClass ( "class.tpl", $crudControllerName . "Events", $ns, $uses, "extends CRUDEvents", "\t//use override/implement Methods" );
	}

	protected function createCRUDFilesClass($crudControllerName, $classContent = "") {
		$ns = Startup::getNS ( "controllers" ) . "crud\\files";
		$uses = "\nuse Ubiquity\\controllers\\crud\\CRUDFiles;";
		return $this->_createClass ( "class.tpl", $crudControllerName . "Files", $ns, $uses, "extends CRUDFiles", $classContent );
	}

	protected function createAuthFilesClass($authControllerName, $classContent = "") {
		$ns = Startup::getNS ( "controllers" ) . "auth\\files";
		$uses = "\nuse Ubiquity\\controllers\\auth\\AuthFiles;";
		return $this->_createClass ( "class.tpl", $authControllerName . "Files", $ns, $uses, "extends AuthFiles", $classContent );
	}

	protected function createCrudView($frameworkName, $controllerName, $newName) {
		$folder = \ROOT . \DS . "views" . \DS . $controllerName;
		UFileSystem::safeMkdir ( $folder );
		try {
			$teInstance = Startup::getTempateEngineInstance ();
			if (isset ( $teInstance )) {
				$blocks = $teInstance->getBlockNames ( $frameworkName );
				if (sizeof ( $blocks ) > 0) {
					$content = [ "{% extends \"" . $frameworkName . "\" %}\n" ];
					foreach ( $blocks as $blockname ) {
						$content [] = "{% block " . $blockname . " %}\n\t{{ parent() }}\n{% endblock %}\n";
					}
				} else {
					$content = [ $teInstance->getCode ( $frameworkName ) ];
				}
			}
		} catch ( \Exception $e ) {
			$content = [ $teInstance->getCode ( $frameworkName ) ];
		}
		if (isset ( $content )) {
			return UFileSystem::save ( $folder . \DS . $newName . ".html", implode ( "", $content ) );
		}
	}
}

