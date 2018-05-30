<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ajax\semantic\components\validation\Rule;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\http\UResponse;

/**
 *
 * @author jc
 * @property \Ajax\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 */
trait CreateControllersTrait{
	
	private $views=["CRUD"=>["index"=>"@framework/crud/index.html","form"=>"@framework/crud/form.html","display"=>"@framework/crud/display.html"],
			"auth"=>["index"=>"@framework/auth/index.html","info"=>"@framework/auth/info.html","noAccess"=>"@framework/auth/noAccess.html","disconnected"=>"@framework/auth/disconnected.html","message"=>"@framework/auth/message.html","baseTemplate"=>"@framework/auth/baseTemplate.html"]
	];
	
	abstract protected function _createMethod($access,$name,$parameters="",$return="",$content="",$comment="");
	
	abstract protected function _createClass($template,$classname,$namespace,$uses,$extendsOrImplements,$classContent);
	
	abstract public function _getAdminFiles();

	abstract protected function _createController($controllerName,$variables=[],$ctrlTemplate='controller.tpl',$hasView=false,$jsCallback="");
	

	public function frmAddCrudController(){
		$config=Startup::getConfig();
		$resources=CacheManager::getModels($config, true);
		$resources=\array_combine($resources, $resources);
		$resourcesList=$this->jquery->semantic()->htmlDropdown("resources-list","",$resources);
		$resourcesList->asSelect("crud-model");
		$viewList=$this->jquery->semantic()->htmlDropdown("view-list","",$this->views["CRUD"]);
		$viewList->asSelect("crud-views",true);
		$viewList->setDefaultText("Select views");
		$viewList->setProperty("style", "display: none;");
		$frm=$this->jquery->semantic()->htmlForm("crud-controller-frm");
		$frm->addExtraFieldRule("crud-model", "exactCount[1]");
		$frm->addExtraFieldRules("crud-name", ["empty",["checkController","Controller {value} already exists!"]]);
		$this->jquery->exec(Rule::ajax($this->jquery, "checkController", $this->_getAdminFiles()->getAdminBaseRoute() . "/_controllerExists/crud-name", "{}", "result=data.result;", "postForm", [ "form" => "crud-controller-frm" ]), true);
		
		$frm->setValidationParams(["on"=>"blur","inline"=>true]);
		$frm->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute()."/addCrudController","#frm");
		$this->jquery->change("#ck-add-route", "$('#div-new-route').toggle($(this).is(':checked'));");
		$this->jquery->jsonOn("change", "#ck-add-route", $this->_getAdminFiles()->getAdminBaseRoute() . "/_addCtrlRoute/crud", "post", [ "context" => "$('#crud-controller-frm')","params" => "$('#crud-controller-frm').serialize()","jsCondition" => "$('#ck-add-route').is(':checked')" ]);
		
		$this->jquery->click("#validate-btn",'$("#crud-controller-frm").form("submit");');
		$this->jquery->execOn("click", "#cancel-btn", '$("#frm").html("");');
		$this->jquery->exec("$('#crud-datas-ck').checkbox();",true);
		
		$this->jquery->exec("$('#crud-viewer-ck').checkbox();",true);
		$this->jquery->exec("$('#crud-events-ck').checkbox();",true);
		$this->jquery->exec("$('#ck-add-route').checkbox();",true);
		
		
		$this->jquery->exec('$("#crud-files-ck").checkbox({onChange:function(){ $("#view-list").toggle($("#crud-files-ck").checkbox("is checked"));}});',true);
		$this->jquery->renderView($this->_getAdminFiles()->getViewAddCrudController(),["controllerNS"=>Startup::getNS ( "controllers" )]);
	}
	
	public function _addCtrlRoute($type){
		if (URequest::isPost()) {
			$result=[ ];
			UResponse::asJSON();
			
			$controller="\\".$_POST[$type."-name"];
			$controller=ClassUtils::getClassSimpleName($controller);
			$result["route-path"]=$controller;
			echo json_encode($result);
		}
	}
	
	public function addCrudController(){
		$classContent="";
		$route="";$routeName="";
		$uses=[];
		$controllerNS=Startup::getNS("controllers");
		if(URequest::isPost()){
			$messages=[];
			$crudControllerName=ucfirst($_POST["crud-name"]);
			$routeName=$crudControllerName;
			$resource=UString::doubleBackSlashes($_POST["crud-model"]);
			$this->_createMethod("public", "__construct","","","\n\t\tparent::__construct();\n\$this->model=\"{$resource}\";");
			
			if(isset($_POST["crud-datas"])){
				$uses[]="use controllers\\crud\\datas\\{$crudControllerName}Datas;";
				$uses[]="use Ubiquity\\controllers\\crud\\CRUDDatas;";
				
				$classContent.=$this->_createMethod("protected", "getAdminData","",": CRUDDatas","\t\treturn new {$crudControllerName}Datas(\$this);");
				$messages[]=$this->createCRUDDatasClass($crudControllerName);
			}
			
			if(isset($_POST["crud-viewer"])){
				$uses[]="use controllers\\crud\\viewers\\{$crudControllerName}Viewer;";
				$uses[]="use Ubiquity\\controllers\\admin\\viewers\\ModelViewer;";
				
				$classContent.=$this->_createMethod("protected", "getModelViewer","",": ModelViewer","\t\treturn new {$crudControllerName}Viewer(\$this);");
				$messages[]=$this->createModelViewerClass($crudControllerName);
			}
			if(isset($_POST["crud-events"])){
				$uses[]="use controllers\\crud\\events\\{$crudControllerName}Events;";
				$uses[]="use Ubiquity\\controllers\\crud\\CRUDEvents;";
				
				$classContent.=$this->_createMethod("protected", "getEvents","",": CRUDEvents","\t\treturn new {$crudControllerName}Events(\$this);");
				$messages[]=$this->createEventsClass($crudControllerName);
			}
			
			if(isset($_POST["crud-files"])){
				$uses[]="use controllers\\crud\\files\\{$crudControllerName}Files;";
				$uses[]="use Ubiquity\\controllers\\crud\\CRUDFiles;";
				$classContent.=$this->_createMethod("protected", "getFiles","",": CRUDFiles","\t\treturn new {$crudControllerName}Files();");
				$crudFiles=$_POST["crud-views"];
				$crudFiles=explode(",", $crudFiles);
				$classFilesContent=[];
				foreach ($crudFiles as $file){
					if(isset($this->views["CRUD"][$file])){
						$frameworkViewname=$this->views["CRUD"][$file];
						$this->createCrudView($frameworkViewname,$crudControllerName, $file);
						$classFilesContent[]=$this->_createMethod("public", "getView".ucfirst($file),"","","\t\treturn \"".$crudControllerName."/".$file.".html\";");
					}
				}
				$messages[]=$this->createCRUDFilesClass($crudControllerName,implode("",$classFilesContent));
			}
			if(isset($_POST["ck-add-route"])){
				$route=$_POST["route-path"];
				if(UString::isNotNull($route)){
					if(!UString::startswith($route, "/")){
						$route="/".$route;
					}
					$routeName=$route;
					$route="\n * @route(\"{$route}\",\"inherited\"=>true,\"automated\"=>true)";
				}
			}
			$uses=implode("\n", $uses);
			$messages[]=$this->_createController($crudControllerName,["%routeName%"=>$routeName,"%route%"=>$route,"%resource%"=>$resource,"%uses%"=>$uses,"%namespace%"=>$controllerNS,"%baseClass%"=>"\\Ubiquity\\controllers\\crud\\CRUDController","%content%"=>$classContent],"crudController.tpl");
			echo implode("", $messages);
			$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute() . "/_refreshControllers/refresh", "#dtControllers", [ "jqueryDone" => "replaceWith","hasLoader" => false,"dataType" => "html" ]);
			echo $this->jquery->compile($this->view);
		}
	}
	
	public function frmAddAuthController(){
		$config=Startup::getConfig();
		$viewList=$this->jquery->semantic()->htmlDropdown("view-list","",$this->views["auth"]);
		$viewList->asSelect("auth-views",true);
		$viewList->setDefaultText("Select views");
		$viewList->setProperty("style", "display: none;");
		$authControllers=CacheManager::getControllers("Ubiquity\\controllers\\auth\\AuthController",false,true);
		$authControllers=array_combine($authControllers, $authControllers);
		$ctrlList=$this->jquery->semantic()->htmlDropdown("ctrl-list","Ubiquity\\controllers\\auth\\AuthController",$authControllers);
		$ctrlList->asSelect("baseClass");
		$ctrlList->setDefaultText("Select base class");
		
		$frm=$this->jquery->semantic()->htmlForm("auth-controller-frm");
		$frm->addExtraFieldRules("auth-name", ["empty",["checkController","Controller {value} already exists!"]]);
		$this->jquery->exec(Rule::ajax($this->jquery, "checkController", $this->_getAdminFiles()->getAdminBaseRoute() . "/_controllerExists/auth-name", "{}", "result=data.result;", "postForm", [ "form" => "auth-controller-frm" ]), true);
		
		$frm->setValidationParams(["on"=>"blur","inline"=>true]);
		$frm->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute()."/addAuthController","#frm");
		$this->jquery->change("#ck-add-route", "$('#div-new-route').toggle($(this).is(':checked'));");
		$this->jquery->jsonOn("change", "#ck-add-route", $this->_getAdminFiles()->getAdminBaseRoute() . "/_addCtrlRoute/auth", "post", [ "context" => "$('#auth-controller-frm')","params" => "$('#auth-controller-frm').serialize()","jsCondition" => "$('#ck-add-route').is(':checked')" ]);
		
		$this->jquery->click("#validate-btn",'$("#auth-controller-frm").form("submit");');
		$this->jquery->execOn("click", "#cancel-btn", '$("#frm").html("");');
		$this->jquery->exec("$('#ck-add-route').checkbox();",true);
		$this->jquery->exec('$("#auth-files-ck").checkbox({onChange:function(){ $("#view-list").toggle($("#auth-files-ck").checkbox("is checked"));}});',true);
		$this->jquery->renderView($this->_getAdminFiles()->getViewAddAuthController(),["controllerNS"=>Startup::getNS ( "controllers" )]);
	}
	
	public function addAuthController(){

		if(URequest::isPost()){
			$classContent="";
			$route="";$routeName="";
			$baseClass="\\".$_POST["baseClass"];
			
			if($baseClass=="\\Ubiquity\\controllers\\auth\\AuthController"){
				$controllerTemplate="authController.tpl";
				$uses=["use Ubiquity\\controllers\\Startup;","use Ubiquity\\utils\\http\\USession;","use Ubiquity\\utils\\http\\URequest;"];
			}else{
				$controllerTemplate="authController_.tpl";
				$uses=[];
			}
			$controllerNS=Startup::getNS("controllers");
			
			$messages=[];
			$authControllerName=ucfirst($_POST["auth-name"]);
			$routeName=$authControllerName;
			if(isset($_POST["auth-files"])){
				$uses[]="use controllers\\auth\\files\\{$authControllerName}Files;";
				$uses[]="use Ubiquity\\controllers\\auth\\AuthFiles;";
				$classContent.=$this->_createMethod("protected", "getFiles","",": AuthFiles","\t\treturn new {$authControllerName}Files();");
				$authFiles=$_POST["auth-views"];
				$authFiles=explode(",", $authFiles);
				$classFilesContent=[];
				foreach ($authFiles as $file){
					if(isset($this->views["auth"][$file])){
						$frameworkViewname=$this->views["auth"][$file];
						$this->createCrudView($frameworkViewname,$authControllerName, $file);
						$classFilesContent[]=$this->_createMethod("public", "getView".ucfirst($file),"","","\t\treturn \"".$authControllerName."/".$file.".html\";");
					}
				}
				$messages[]=$this->createAuthFilesClass($authControllerName,implode("",$classFilesContent));
			}
			if(isset($_POST["ck-add-route"])){
				$route=$_POST["route-path"];
				if(UString::isNotNull($route)){
					if(!UString::startswith($route, "/")){
						$route="/".$route;
					}
					$routeName=$route;
					$route="\n * @route(\"{$route}\",\"inherited\"=>true,\"automated\"=>true)";
				}
			}
			$uses=implode("\n", $uses);
			$messages[]=$this->_createController($authControllerName,["%routeName%"=>$routeName,"%route%"=>$route,"%uses%"=>$uses,"%namespace%"=>$controllerNS,"%baseClass%"=>$baseClass,"%content%"=>$classContent],$controllerTemplate);
			echo implode("", $messages);
			$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute() . "/_refreshControllers/refresh", "#dtControllers", [ "jqueryDone" => "replaceWith","hasLoader" => false,"dataType" => "html" ]);
			echo $this->jquery->compile($this->view);
		}
	}
	
	protected function createCRUDDatasClass($crudControllerName){
		$ns=Startup::getNS("controllers")."crud\\datas";
		$uses="\nuse Ubiquity\\controllers\\crud\\CRUDDatas;";
		return $this->_createClass("class.tpl", $crudControllerName."Datas", $ns, $uses, "extends CRUDDatas", "\t//use override/implement Methods");
	}
	
	protected function createModelViewerClass($crudControllerName){
		$ns=Startup::getNS("controllers")."crud\\viewers";
		$uses="\nuse Ubiquity\\controllers\\admin\\viewers\\ModelViewer;";
		return $this->_createClass("class.tpl", $crudControllerName."Viewer", $ns, $uses, "extends ModelViewer", "\t//use override/implement Methods");
	}
	
	protected function createEventsClass($crudControllerName){
		$ns=Startup::getNS("controllers")."crud\\events";
		$uses="\nuse Ubiquity\\controllers\\crud\\CRUDEvents;";
		return $this->_createClass("class.tpl", $crudControllerName."Events", $ns, $uses, "extends CRUDEvents", "\t//use override/implement Methods");
	}
	
	protected function createCRUDFilesClass($crudControllerName,$classContent=""){
		$ns=Startup::getNS("controllers")."crud\\files";
		$uses="\nuse Ubiquity\\controllers\\crud\\CRUDFiles;";
		return $this->_createClass("class.tpl", $crudControllerName."Files", $ns, $uses, "extends CRUDFiles", $classContent);
	}
	
	protected function createAuthFilesClass($authControllerName,$classContent=""){
		$ns=Startup::getNS("controllers")."auth\\files";
		$uses="\nuse Ubiquity\\controllers\\auth\\AuthFiles;";
		return $this->_createClass("class.tpl", $authControllerName."Files", $ns, $uses, "extends AuthFiles", $classContent);
	}
	
	protected function createCrudView($frameworkName,$controllerName,$newName){
		$folder=ROOT . DS . "views" . DS .$controllerName;
		UFileSystem::safeMkdir($folder);
		try{
			$blocks=$this->view->getBlockNames($frameworkName);
			if(sizeof($blocks)>0){
				$content=["{% extends \"".$frameworkName."\" %}\n"];
				foreach ($blocks as $blockname){
					$content[]="{% block ".$blockname." %}\n\t{{ parent() }}\n{% endblock %}\n";
				}
			}else{
				$content=[$this->view->getCode($frameworkName)];
			}
		}catch(\Exception $e){
			$content=[$this->view->getCode($frameworkName)];
		}
		return UFileSystem::save($folder. DS .$newName.".html", implode("", $content));
	}
}
