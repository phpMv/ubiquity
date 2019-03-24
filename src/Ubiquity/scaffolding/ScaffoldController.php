<?php

namespace Ubiquity\scaffolding;

use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\base\UString;
use Ubiquity\controllers\admin\utils\CodeUtils;
use Ubiquity\utils\base\UIntrospection;
use Ubiquity\cache\ClassUtils;
use Ubiquity\scaffolding\creators\AuthControllerCreator;
use Ubiquity\scaffolding\creators\CrudControllerCreator;
use Ubiquity\scaffolding\creators\RestControllerCreator;

/**
 * Base class for Scaffolding.
 * Ubiquity\scaffolding$ScaffoldController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
abstract class ScaffoldController {
	protected $config;
	public static $views = [
							"CRUD" => [ "index" => "@framework/crud/index.html","form" => "@framework/crud/form.html","display" => "@framework/crud/display.html" ],
							"auth" => [ "index" => "@framework/auth/index.html","info" => "@framework/auth/info.html","noAccess" => "@framework/auth/noAccess.html","disconnected" => "@framework/auth/disconnected.html","message" => "@framework/auth/message.html","baseTemplate" => "@framework/auth/baseTemplate.html" ] ];

	public abstract function getTemplateDir();

	public function _refreshRest($refresh = false) {
	}

	public function initRestCache($refresh = true) {
	}

	protected abstract function storeControllerNameInSession($controller);

	public abstract function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null);

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
		$crudController = new CrudControllerCreator ( $crudControllerName, $resource, $crudDatas, $crudViewer, $crudEvents, $crudViews, $routePath );
		$crudController->create ( $this );
	}

	public function addAuthController($authControllerName, $baseClass, $authViews = null, $routePath = "") {
		$authCreator = new AuthControllerCreator ( $authControllerName, $baseClass, $authViews, $routePath );
		$authCreator->create ( $this );
	}

	public function addRestController($restControllerName, $baseClass, $resource, $routePath = "", $reInit = true) {
		$restCreator = new RestControllerCreator ( $restControllerName, $baseClass, $resource, $routePath );
		$restCreator->create ( $this, $reInit );
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

	public function _newAction($controller, $action, $parameters = null, $content = '', $routeInfo = null, $createView = false, $theme = null) {
		$templateDir = $this->getTemplateDir ();
		$msgContent = "";
		$r = new \ReflectionClass ( $controller );
		if (! method_exists ( $controller, $action )) {
			$ctrlFilename = $r->getFileName ();
			$content = CodeUtils::indent ( $content, 2 );
			$classCode = UIntrospection::getClassCode ( $controller );
			if ($classCode !== false) {
				$fileContent = \implode ( "", $classCode );
				$fileContent = \trim ( $fileContent );
				$posLast = \strrpos ( $fileContent, "}" );
				if ($posLast !== false) {
					if ($createView) {
						$viewname = $this->_createViewOp ( ClassUtils::getClassSimpleName ( $controller ), $action, $theme );
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
					$actionContent = UFileSystem::openReplaceInTemplateFile ( $templateDir . "action.tpl", [ "%route%" => "\n" . $routeAnnotation ?? '',"%actionName%" => $action,"%parameters%" => $parameters,"%content%" => $content ] );
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
			} else {
				echo $this->showSimpleMessage ( "The action {$action} already exists in {$controller}!", "error", "Creation", "warning circle", null, "msgControllers" );
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

	protected function _createViewOp($controller, $action, $theme = null) {
		$prefix = "";
		if (! isset ( $theme ) || $theme == '') {
			$theme = $this->config ["templateEngineOptions"] ["activeTheme"] ?? null;
		}
		if ($theme != null) {
			$prefix = 'themes/' . $theme . '/';
		}
		$viewName = $prefix . $controller . "/" . $action . ".html";
		UFileSystem::safeMkdir ( \ROOT . \DS . "views" . \DS . $prefix . $controller );
		$templateDir = $this->getTemplateDir ();
		UFileSystem::openReplaceWriteFromTemplateFile ( $templateDir . "view.tpl", \ROOT . \DS . "views" . \DS . $viewName, [ "%controllerName%" => $controller,"%actionName%" => $action ] );
		return $viewName;
	}

	public function createAuthCrudView($frameworkName, $controllerName, $newName) {
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

	public function setConfig($config) {
		$this->config = $config;
	}
}

