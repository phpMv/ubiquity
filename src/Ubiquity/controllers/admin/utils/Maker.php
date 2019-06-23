<?php
namespace Ubiquity\controllers\admin\utils;

use Ubiquity\utils\base\UIntrospection;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\base\CodeUtils;

class Maker {

	private static $baseDir;

	public static function init($baseDir) {
		self::$baseDir = $baseDir;
	}

	public static function createAction($controller, $action, $parameters = "", $content = "", $createView = false, $route = []) {
		$msgContent = [];
		$hasErrors = false;
		$r = new \ReflectionClass($controller);
		$ctrlFilename = $r->getFileName();
		$content = CodeUtils::indent($content, 2);
		$fileContent = \implode("", UIntrospection::getClassCode($controller));
		$fileContent = \trim($fileContent);
		$posLast = \strrpos($fileContent, "}");
		if ($posLast !== false) {
			if ($createView) {
				$viewname = self::createView(ClassUtils::getClassSimpleName($controller), $action);
				$content .= "\n\t\t\$this->loadView('" . $viewname . "');\n";
				$msgContent[] = "<br>Created view : <b>" . $viewname . "</b>";
			}
			$routeAnnotation = "";
			if (isset($route["path"])) {
				$name = "route";
				$path = $route["path"];
				$routeProperties = [
					'"' . $path . '"'
				];
				$methods = $route["methods"];
				if (UString::isNotNull($methods)) {
					$routeProperties[] = '"methods"=>' . self::getMethods($methods);
				}
				if ($route["cache"]) {
					$routeProperties[] = '"cache"=>true';
					if (isset($route["duration"])) {
						$duration = $route["duration"];
						if (\ctype_digit($duration)) {
							$routeProperties[] = '"duration"=>' . $duration;
						}
					}
				}
				$routeProperties = \implode(",", $routeProperties);
				$routeAnnotation = UFileSystem::openReplaceInTemplateFile(self::$baseDir . "/admin/templates/annotation.tpl", [
					"%name%" => $name,
					"%properties%" => $routeProperties
				]);
			}
			$parameters = CodeUtils::cleanParameters($parameters);
			$actionContent = UFileSystem::openReplaceInTemplateFile(self::$baseDir . "/admin/templates/action.tpl", [
				"%route%" => "\n" . $routeAnnotation,
				"%actionName%" => $action,
				"%parameters%" => $parameters,
				"%content%" => $content
			]);
			$fileContent = \substr_replace($fileContent, "\n%content%", $posLast - 1, 0);
			if (! CodeUtils::isValidCode('<?php ' . $content)) {
				$msgContent = [
					"Errors parsing action content!"
				];
				$hasErrors = true;
			} else {
				if (UFileSystem::replaceWriteFromContent($fileContent . "\n", $ctrlFilename, [
					'%content%' => $actionContent
				])) {
					$msgContent[] = "The action <b>{$action}</b> is created in controller <b>{$controller}</b>";
				}
			}
		}
		return compact($msgContent, $hasErrors);
	}

	public static function createView($controller, $action) {
		$viewName = $controller . "/" . $action . ".html";
		UFileSystem::safeMkdir(\ROOT . \DS . "views" . \DS . $controller);
		UFileSystem::openReplaceWriteFromTemplateFile(self::$baseDir . "/admin/templates/view.tpl", \ROOT . \DS . "views" . \DS . $viewName, [
			"%controllerName%" => $controller,
			"%actionName%" => $action
		]);
		return $viewName;
	}

	private static function getMethods($strMethods) {
		$methods = \explode(",", $strMethods);
		$result = [];
		foreach ($methods as $method) {
			$result[] = '"' . $method . '"';
		}
		return "[" . \implode(",", $result) . "]";
	}
}
