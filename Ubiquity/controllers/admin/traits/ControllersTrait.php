<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UIntrospection;
use Ubiquity\controllers\admin\utils\CodeUtils;
use Ubiquity\controllers\admin\utils\Constants;
use Ajax\semantic\components\validation\Rule;
use Ubiquity\controllers\Router;
use Ubiquity\utils\base\UString;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\utils\http\USession;

/**
 *
 * @author jc
 * @property \Ajax\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 */
trait ControllersTrait{

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getAdminFiles();

	abstract public function controllers();
	
	abstract public function _refreshControllers($refresh = false);

	abstract protected function _createController($controllerName,$variables=[],$ctrlTemplate='controller.tpl',$hasView=false,$jsCallback="");
	
	abstract protected function _addMessageForRouteCreation($path);

	abstract public function showSimpleMessage($content, $type, $title=null,$icon="info", $timeout=NULL, $staticName=null):HtmlMessage;

	public function createController($force=null) {
		if (URequest::isPost()) {
			$this->_createController($_POST["name"],["%baseClass%"=>"ControllerBase"],'controller.tpl',isset($_POST["lbl-ck-div-name"]));
		}
		$this->controllers();
	}

	public function _createView() {
		if (URequest::isPost()) {
			$action=$_POST["action"];
			$controller=$_POST["controller"];
			$controllerFullname=$_POST["controllerFullname"];
			$viewName=$controller . "/" . $action . ".html";
			$this->_createViewOp($controller, $action);
			if (\file_exists(ROOT . DS . "views" . DS . $viewName)) {
				$this->jquery->exec('$("#msgControllers").transition("show");$("#msgControllers .content").transition("show").append("<br><b>' . $viewName . '</b> created !");', true);
			}
			$r=new \ReflectionMethod($controllerFullname, $action);
			$lines=file($r->getFileName());
			$views=$this->_getAdminViewer()->getActionViews($controllerFullname, $controller, $action, $r, $lines);
			foreach ( $views as $view ) {
				echo $view->compile($this->jquery);
				echo "&nbsp;";
			}
			echo $this->jquery->compile($this->view);
		}
	}

	private function _createViewOp($controller, $action) {
		$viewName=$controller . "/" . $action . ".html";
		UFileSystem::safeMkdir(ROOT . DS . "views" . DS . $controller);
		$frameworkDir=Startup::getFrameworkDir();
		UFileSystem::openReplaceWriteFromTemplateFile($frameworkDir . "/admin/templates/view.tpl", ROOT . DS . "views" . DS . $viewName, [ "%controllerName%" => $controller,"%actionName%" => $action ]);
		return $viewName;
	}

	public function _newActionFrm() {
		if (URequest::isPost()) {
			$controllers=CacheManager::getControllers();
			$controller=$_POST["controller"];
			$modal=$this->jquery->semantic()->htmlModal("modalNewAction", "Creating a new action in controller");
			$modal->setInverted();
			$frm=$this->jquery->semantic()->htmlForm("frmNewAction");
			$dd=$frm->addDropdown('controller', \array_combine($controllers, $controllers), "Controller", $controller);
			$dd->getField()->setShowOnFocus(false);
			$fields=$frm->addFields([ "action","parameters" ], "Action & parameters");
			$fields->getItem(0)->addRules([ "empty",[ "checkAction","Action {value} already exists!" ] ]);
			$frm->addTextarea("content", "Implementation")->addRule([ "checkContent","Errors parsing action content!" ]);
			;
			$frm->addCheckbox("ck-view", "Create associated view");
			$frm->addCheckbox("ck-add-route", "Add route...");

			$frm->addContent("<div id='div-new-route' style='display: none;'>");
			$frm->addDivider();
			$fields=$frm->addFields();
			$fields->addInput("path", "", "text", "")->addRule([ "checkRoute","Route {value} already exists!" ]);
			$fields->addDropdown("methods", Constants::REQUEST_METHODS, null, "", true);
			$duration=$fields->addInput("duration", "", "number");
			$ck=$duration->labeledCheckbox("left", null);
			$ck->getField()->setProperty("name", "ck-Cache");
			$frm->addContent("</div>");

			$frm->setValidationParams([ "on" => "blur","inline" => true ]);
			$frm->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute() . "/_newAction", "#messages");
			$modal->setContent($frm);
			$modal->addAction("Validate");
			$this->jquery->click("#action-modalNewAction-0", "$('#frmNewAction').form('submit');", false, false);
			$modal->addAction("Close");
			$this->jquery->exec("$('.dimmer.modals.page').html('');$('#modalNewAction').modal('show');", true);
			$this->jquery->jsonOn("change", "#ck-add-route", $this->_getAdminFiles()->getAdminBaseRoute() . "/_addRouteWithNewAction", "post", [ "context" => "$('#frmNewAction')","params" => "$('#frmNewAction').serialize()","jsCondition" => "$('#ck-add-route').is(':checked')" ]);
			$this->jquery->exec(Rule::ajax($this->jquery, "checkAction", $this->_getAdminFiles()->getAdminBaseRoute() . "/_methodExists", "{}", "result=data.result;", "postForm", [ "form" => "frmNewAction" ]), true);
			$this->jquery->exec(Rule::ajax($this->jquery, "checkContent", $this->_getAdminFiles()->getAdminBaseRoute() . "/_checkContent", "{}", "result=data.result;", "postForm", [ "form" => "frmNewAction" ]), true);
			$this->jquery->exec(Rule::ajax($this->jquery, "checkRoute", $this->_getAdminFiles()->getAdminBaseRoute() . "/_checkRoute", "{}", "result=data.result;", "postForm", [ "form" => "frmNewAction" ]), true);
			$this->jquery->change("#ck-add-route", "$('#div-new-route').toggle($(this).is(':checked'));");
			echo $modal;
			echo $this->jquery->compile($this->view);
		}
	}
	
	public function _controllerExists($fieldname) {
		if (URequest::isPost()) {
			$result=[ ];
			header('Content-type: application/json');
			$controller=ucfirst($_POST[$fieldname]);
			$controllerNS=Startup::getNS("controllers");
			$result["result"]=!class_exists($controllerNS.$controller);
			echo json_encode($result);
		}
	}

	public function _methodExists() {
		if (URequest::isPost()) {
			$result=[ ];
			header('Content-type: application/json');
			$controller=$_POST["controller"];
			$action=$_POST["action"];
			if (\method_exists($controller, $action)) {
				$result["result"]=false;
			} else {
				$result["result"]=true;
			}
			echo json_encode($result);
		}
	}

	public function _checkContent() {
		if (URequest::isPost()) {
			$result=[ ];
			header('Content-type: application/json');
			$content=$_POST["content"];
			$result["result"]=CodeUtils::isValidCode('<?php ' . $content);
			echo json_encode($result);
		}
	}

	public function _checkRoute() {
		if (URequest::isPost()) {
			$result=[ ];
			header('Content-type: application/json');
			$path=$_POST["path"];
			$routes=CacheManager::getRoutes();
			$result["result"]=!(isset($routes[$path]) || Router::getRouteInfo($path) !== false);
			echo json_encode($result);
		}
	}

	public function _addRouteWithNewAction() {
		if (URequest::isPost()) {
			$result=[ ];
			header('Content-type: application/json');

			$controller=$_POST["controller"];
			$action=$_POST["action"];
			$parameters=$_POST["parameters"];
			$parameters=CodeUtils::getParametersForRoute($parameters);
			$controller=ClassUtils::getClassSimpleName($controller);

			$urlParts=\array_diff(\array_merge([ $controller,$action ], $parameters), [ "","{}" ]);
			$result["path"]=\implode('/', $urlParts);
			echo json_encode($result);
		}
	}

	public function _newAction() {
		if (URequest::isPost()) {
			$frameworkDir=Startup::getFrameworkDir();
			$msgContent="";
			$controller=$_POST["controller"];
			$r=new \ReflectionClass($controller);
			$ctrlFilename=$r->getFileName();
			$action=$_POST["action"];
			$parameters=$_POST["parameters"];
			$content=$_POST["content"];
			$content=CodeUtils::indent($content, 2);
			$createView=isset($_POST["ck-view"]);
			$createRoute=isset($_POST["ck-add-route"]);
			$fileContent=\implode("", UIntrospection::getClassCode($controller));
			$fileContent=\trim($fileContent);
			$posLast=\strrpos($fileContent, "}");
			if ($posLast !== false) {
				if ($createView) {
					$viewname=$this->_createViewOp(ClassUtils::getClassSimpleName($controller), $action);
					$content.="\n\t\t\$this->loadView('" . $viewname . "');\n";
					$msgContent.="<br>Created view : <b>" . $viewname . "</b>";
				}
				$routeAnnotation="";
				if ($createRoute) {
					$name="route";
					$path=$_POST["path"];
					$routeProperties=[ '"' . $path . '"' ];
					$methods=$_POST["methods"];
					if (UString::isNotNull($methods)) {
						$routeProperties[]='"methods"=>' . $this->getMethods($methods);
					}
					if (isset($_POST["ck-Cache"])) {
						$routeProperties[]='"cache"=>true';
						if (isset($_POST["duration"])) {
							$duration=$_POST["duration"];
							if (\ctype_digit($duration)) {
								$routeProperties[]='"duration"=>' . $duration;
							}
						}
					}
					$routeProperties=\implode(",", $routeProperties);
					$routeAnnotation=UFileSystem::openReplaceInTemplateFile($frameworkDir . "/admin/templates/annotation.tpl", [ "%name%" => $name,"%properties%" => $routeProperties ]);

					$msgContent.=$this->_addMessageForRouteCreation($path);
				}
				$parameters=CodeUtils::cleanParameters($parameters);
				$actionContent=UFileSystem::openReplaceInTemplateFile($frameworkDir . "/admin/templates/action.tpl", [ "%route%" => "\n" . $routeAnnotation,"%actionName%" => $action,"%parameters%" => $parameters,"%content%" => $content ]);
				$fileContent=\substr_replace($fileContent, "\n%content%", $posLast - 1, 0);
				if (!CodeUtils::isValidCode('<?php ' . $content)) {
					echo $this->showSimpleMessage("Errors parsing action content!", "warning","Creation", "warning circle", null, "msgControllers");
					echo $this->jquery->compile($this->view);
					return;
				} else {
					if (UFileSystem::replaceWriteFromContent($fileContent . "\n", $ctrlFilename, [ '%content%' => $actionContent ])) {
						$msgContent="The action <b>{$action}</b> is created in controller <b>{$controller}</b>" . $msgContent;
						echo $this->showSimpleMessage($msgContent, "info","Creation", "info circle", null, "msgControllers");
					}
				}
			}
		}
		$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute() . "/_refreshControllers/refresh", "#dtControllers", [ "jqueryDone" => "replaceWith","hasLoader" => false,"dataType" => "html" ]);
		echo $this->jquery->compile($this->view);
	}

	public function _refreshCacheControllers() {
		$config=Startup::getConfig();
		\ob_start();
		CacheManager::initCache($config, "controllers");
		$message=\ob_get_clean();
		echo $this->showSimpleMessage(\nl2br($message), "info", "info", 4000);
		$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute() . "/_refreshControllers/refresh", "#dtControllers", [ "jqueryDone" => "replaceWith","hasLoader" => false,"dataType" => "html" ]);
		echo $this->jquery->compile($this->view);
	}

	private function getMethods($strMethods) {
		$methods=\explode(",", $strMethods);
		$result=[ ];
		foreach ( $methods as $method ) {
			$result[]='"' . $method . '"';
		}
		return "[" . \implode(",", $result) . "]";
	}
	
	public function frmFilterControllers(){
		$controllers=CacheManager::getControllers();
		$this->_getAdminViewer()->getFilterControllers($controllers);
		$this->jquery->postFormOn("click", "#validate-btn", $this->_getAdminFiles()->getAdminBaseRoute()."/filterControllers", "filtering-frm","#dtControllers",["jqueryDone" => "replaceWith","hasLoader" => false,"jsCallback"=>'$("#frm").html("");']);
		$this->jquery->execOn("click", "#cancel-btn", '$("#frm").html("");');
		$this->jquery->renderView($this->_getAdminFiles()->getViewControllersFiltering());
	}
	
	protected function _createClass($template,$classname,$namespace,$uses,$extendsOrImplements,$classContent){
		$namespaceVar="";
		if(UString::isNotNull($namespace)){
			$namespaceVar="namespace {$namespace};";
		}
		$variables=["%classname%"=>$classname,"%namespace%"=>$namespaceVar,"%uses%"=>$uses,"%extendsOrImplements%"=>$extendsOrImplements,"%classContent%"=>$classContent];
		$frameworkDir = Startup::getFrameworkDir ();
		$directory=UFileSystem::getDirFromNamespace($namespace);
		UFileSystem::safeMkdir($directory);
		$filename=UFileSystem::cleanFilePathname($directory.DS.lcfirst($classname).".php");
		if(!file_exists($filename)){
			UFileSystem::openReplaceWriteFromTemplateFile ( $frameworkDir . "/admin/templates/" . $template, $filename, $variables );
			$message = $this->showSimpleMessage ( "The <b>" . $classname . "</b> class has been created in <b>" . $filename . "</b>.", "success","Creation", "checkmark circle");
		}else{
			$message = $this->showSimpleMessage ( "The file <b>" . $filename . "</b> already exists.<br>Can not create the <b>" . $classname . "</b> class!", "warning","Creation", "warning circle");
		}
		return $message;
	}
	
	protected function _createMethod($access,$name,$parameters="",$return="",$content="",$comment=""){
		$frameworkDir = Startup::getFrameworkDir ();
		$keyAndValues=["%access%"=>$access,"%name%"=>$name,"%parameters%"=>$parameters,"%content%"=>$content,"%comment%"=>$comment,"%return%"=>$return];
		return UFileSystem::openReplaceInTemplateFile($frameworkDir . "/admin/templates/method.tpl" , $keyAndValues);
	}
	
	public function filterControllers(){
		USession::set("filtered-controllers", URequest::post("filtered-controllers",[]));
		$this->_refreshControllers("refresh");
	}
}
