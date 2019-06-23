<?php
namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\components\validation\Rule;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\admin\utils\Constants;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\base\CodeUtils;

/**
 *
 * @author jc
 * @property \Ajax\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 * @property \Ubiquity\scaffolding\AdminScaffoldController $scaffold
 */
trait ControllersTrait {

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getFiles();

	abstract public function controllers();

	abstract public function _refreshControllers($refresh = false);

	abstract protected function _createController($controllerName, $variables = [], $ctrlTemplate = 'controller.tpl', $hasView = false, $jsCallback = "");

	abstract protected function _addMessageForRouteCreation($path);

	abstract public function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	public function createController($force = null) {
		if (URequest::isPost()) {
			$this->_createController($_POST["name"], [
				"%baseClass%" => "ControllerBase"
			], 'controller.tpl', isset($_POST["lbl-ck-div-name"]));
		}
		$this->controllers();
	}

	public function _createView() {
		if (URequest::isPost()) {
			$action = $_POST["action"];
			$controller = $_POST["controller"];
			$controllerFullname = $_POST["controllerFullname"];
			$viewName = $controller . "/" . $action . ".html";
			$this->_createViewOp($controller, $action);
			if (\file_exists(\ROOT . \DS . "views" . \DS . $viewName)) {
				$this->jquery->exec('$("#msgControllers").transition("show");$("#msgControllers .content").transition("show").append("<br><b>' . $viewName . '</b> created !");', true);
			}
			$r = new \ReflectionMethod($controllerFullname, $action);
			$lines = file($r->getFileName());
			$views = $this->_getAdminViewer()->getActionViews($controllerFullname, $controller, $action, $r, $lines);
			foreach ($views as $view) {
				echo $view->compile($this->jquery);
				echo "&nbsp;";
			}
			echo $this->jquery->compile($this->view);
		}
	}

	private function _createViewOp($controller, $action) {
		$viewName = $controller . "/" . $action . ".html";
		UFileSystem::safeMkdir(\ROOT . \DS . "views" . \DS . $controller);
		$frameworkDir = Startup::getFrameworkDir();
		UFileSystem::openReplaceWriteFromTemplateFile($frameworkDir . "/admin/templates/view.tpl", \ROOT . \DS . "views" . \DS . $viewName, [
			"%controllerName%" => $controller,
			"%actionName%" => $action
		]);
		return $viewName;
	}

	public function _newActionFrm() {
		if (URequest::isPost()) {
			$baseRoute = $this->_getFiles()->getAdminBaseRoute();
			$controllers = CacheManager::getControllers();
			$controller = $_POST["controller"];
			$modal = $this->jquery->semantic()->htmlModal("modalNewAction", "Creating a new action in controller");
			$modal->setInverted();
			$frm = $this->jquery->semantic()->htmlForm("frmNewAction");
			$dd = $frm->addDropdown('controller', \array_combine($controllers, $controllers), "Controller", $controller);
			$dd->getField()->setShowOnFocus(false);
			$fields = $frm->addFields([
				"action",
				"parameters"
			], "Action & parameters");
			$fields->getItem(0)->addRules([
				"empty",
				[
					"checkAction",
					"Action {value} already exists!"
				]
			]);
			$frm->addTextarea("content", "Implementation")->addRule([
				"checkContent",
				"Errors parsing action content!"
			]);
			;
			$frm->addCheckbox("ck-view", "Create associated view");
			$frm->addCheckbox("ck-add-route", "Add route...");

			$frm->addContent("<div id='div-new-route' style='display: none;'>");
			$frm->addDivider();
			$fields = $frm->addFields();
			$fields->addInput("path", "", "text", "")->addRule([
				"checkRoute",
				"Route {value} already exists!"
			]);
			$fields->addDropdown("methods", Constants::REQUEST_METHODS, null, "", true);
			$duration = $fields->addInput("duration", "", "number");
			$ck = $duration->labeledCheckbox("left", null);
			$ck->getField()->setProperty("name", "ck-Cache");
			$frm->addContent("</div>");

			$frm->setValidationParams([
				"on" => "blur",
				"inline" => true
			]);
			$frm->setSubmitParams($baseRoute . "/_newAction", "#messages");
			$modal->setContent($frm);
			$modal->addAction("Validate");
			$this->jquery->click("#action-modalNewAction-0", "$('#frmNewAction').form('submit');", false, false);
			$modal->addAction("Close");
			$this->jquery->exec("$('.dimmer.modals.page').html('');$('#modalNewAction').modal('show');", true);
			$this->jquery->jsonOn("change", "#ck-add-route", $baseRoute . "/_addRouteWithNewAction", "post", [
				"context" => "$('#frmNewAction')",
				"params" => "$('#frmNewAction').serialize()",
				"jsCondition" => "$('#ck-add-route').is(':checked')"
			]);
			$this->jquery->exec(Rule::ajax($this->jquery, "checkAction", $baseRoute . "/_methodExists", "{}", "result=data.result;", "postForm", [
				"form" => "frmNewAction"
			]), true);
			$this->jquery->exec(Rule::ajax($this->jquery, "checkContent", $baseRoute . "/_checkContent", "{}", "result=data.result;", "postForm", [
				"form" => "frmNewAction"
			]), true);
			$this->jquery->exec(Rule::ajax($this->jquery, "checkRoute", $baseRoute . "/_checkRoute", "{}", "result=data.result;", "postForm", [
				"form" => "frmNewAction"
			]), true);
			$this->jquery->change("#ck-add-route", "$('#div-new-route').toggle($(this).is(':checked'));");
			echo $modal;
			echo $this->jquery->compile($this->view);
		}
	}

	public function _controllerExists($fieldname) {
		if (URequest::isPost()) {
			$result = [];
			header('Content-type: application/json');
			$controller = ucfirst($_POST[$fieldname]);
			$controllerNS = Startup::getNS("controllers");
			$result["result"] = ! class_exists($controllerNS . $controller);
			echo json_encode($result);
		}
	}

	public function _methodExists() {
		if (URequest::isPost()) {
			$result = [];
			header('Content-type: application/json');
			$controller = $_POST["controller"];
			$action = $_POST["action"];
			if (\method_exists($controller, $action)) {
				$result["result"] = false;
			} else {
				$result["result"] = true;
			}
			echo json_encode($result);
		}
	}

	public function _checkContent() {
		if (URequest::isPost()) {
			$result = [];
			header('Content-type: application/json');
			$content = $_POST["content"];
			$result["result"] = CodeUtils::isValidCode('<?php ' . $content);
			echo json_encode($result);
		}
	}

	public function _checkRoute() {
		if (URequest::isPost()) {
			$result = [];
			header('Content-type: application/json');
			if (isset($_POST["path"]) && $_POST["path"] != null) {
				$path = $_POST["path"];
				$routes = CacheManager::getRoutes();
				$result["result"] = ! (isset($routes[$path]) || Router::getRouteInfo($path) !== false);
			} else {
				$result["result"] = true;
			}
			echo json_encode($result);
		}
	}

	public function _addRouteWithNewAction() {
		if (URequest::isPost()) {
			$result = [];
			header('Content-type: application/json');

			$controller = $_POST["controller"];
			$action = $_POST["action"];
			$parameters = $_POST["parameters"];
			$parameters = CodeUtils::getParametersForRoute($parameters);
			$controller = ClassUtils::getClassSimpleName($controller);

			$urlParts = \array_diff(\array_merge([
				$controller,
				$action
			], $parameters), [
				"",
				"{}"
			]);
			$result["path"] = \implode('/', $urlParts);
			echo json_encode($result);
		}
	}

	public function _newAction() {
		if (URequest::isPost()) {
			$routeInfo = null;
			if (isset($_POST["ck-add-route"]) && isset($_POST["path"])) {
				$routeInfo = [
					"path" => $_POST["path"],
					"methods" => $_POST["methods"],
					"ck-Cache" => $_POST["ck-Cache"] ?? null,
					"duration" => $_POST["duration"]
				];
			}
			$this->scaffold->_newAction($_POST["controller"], $_POST["action"], $_POST["parameters"], $_POST["content"], $routeInfo, isset($_POST["ck-view"]));
		}
		$this->jquery->get($this->_getFiles()
			->getAdminBaseRoute() . "/_refreshControllers/refresh", "#dtControllers", [
			"jqueryDone" => "replaceWith",
			"hasLoader" => false,
			"dataType" => "html"
		]);
		echo $this->jquery->compile($this->view);
	}

	public function _refreshCacheControllers() {
		$config = Startup::getConfig();
		\ob_start();
		CacheManager::initCache($config, "controllers");
		$message = \ob_get_clean();
		echo $this->showSimpleMessage(\nl2br($message), "info", "info", 4000);
		$this->jquery->get($this->_getFiles()
			->getAdminBaseRoute() . "/_refreshControllers/refresh", "#dtControllers", [
			"jqueryDone" => "replaceWith",
			"hasLoader" => false,
			"dataType" => "html"
		]);
		echo $this->jquery->compile($this->view);
	}

	public function frmFilterControllers() {
		$controllers = CacheManager::getControllers();
		$this->_getAdminViewer()->getFilterControllers($controllers);
		$this->jquery->postFormOn("click", "#validate-btn", $this->_getFiles()
			->getAdminBaseRoute() . "/filterControllers", "filtering-frm", "#dtControllers", [
			"jqueryDone" => "replaceWith",
			"hasLoader" => false,
			"jsCallback" => '$("#frm").html("");'
		]);
		$this->jquery->execOn("click", "#cancel-btn", '$("#frm").html("");');
		$this->jquery->renderView($this->_getFiles()
			->getViewControllersFiltering());
	}

	public function filterControllers() {
		USession::set("filtered-controllers", URequest::post("filtered-controllers", []));
		$this->_refreshControllers("refresh");
	}
}
