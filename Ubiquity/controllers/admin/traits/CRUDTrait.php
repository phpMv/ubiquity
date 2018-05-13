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
trait CRUDTrait{
	
	private $views=["index"=>"@framework/crud/index.html","form"=>"@framework/crud/form.html","display"=>"@framework/crud/display.html"];
	
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
		$viewList=$this->jquery->semantic()->htmlDropdown("view-list","",$this->views);
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
		$this->jquery->jsonOn("change", "#ck-add-route", $this->_getAdminFiles()->getAdminBaseRoute() . "/_addCRUDRoute", "post", [ "context" => "$('#crud-controller-frm')","params" => "$('#crud-controller-frm').serialize()","jsCondition" => "$('#ck-add-route').is(':checked')" ]);
		
		$this->jquery->click("#validate-btn",'$("#crud-controller-frm").form("submit");');
		$this->jquery->execOn("click", "#cancel-btn", '$("#frm").html("");');
		$this->jquery->exec("$('#crud-viewer-ck').checkbox();",true);
		$this->jquery->exec("$('#crud-events-ck').checkbox();",true);
		$this->jquery->exec("$('#ck-add-route').checkbox();",true);
		
		
		$this->jquery->exec('$("#crud-files-ck").checkbox({onChange:function(){ $("#view-list").toggle($("#crud-files-ck").checkbox("is checked"));}});',true);
		$this->jquery->renderView($this->_getAdminFiles()->getViewAddCrudController(),["controllerNS"=>Startup::getNS ( "controllers" )]);
	}
	
	public function _addCRUDRoute(){
		if (URequest::isPost()) {
			$result=[ ];
			UResponse::asJSON();
			
			$controller="\\".$_POST["crud-name"];
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
			if(isset($_POST["crud-viewer"])){
				$uses[]="use controllers\\crud\\viewers\\{$crudControllerName}Viewer;";
				$uses[]="use Ubiquity\\controllers\\admin\\viewers\\ModelViewer;";
				
				$classContent.=$this->_createMethod("protected", "getModelViewer","",": ModelViewer","\n\t\treturn new {$crudControllerName}Viewer(\$this);");
				$messages[]=$this->createModelViewerClass($crudControllerName);
			}
			if(isset($_POST["crud-events"])){
				$uses[]="use controllers\\crud\\events\\{$crudControllerName}Events;";
				$uses[]="use Ubiquity\\controllers\\crud\\CRUDEvents;";
				
				$classContent.=$this->_createMethod("protected", "getEvents","",": CRUDEvents","\n\t\treturn new {$crudControllerName}Events();");
				$messages[]=$this->createEventsClass($crudControllerName);
			}
			
			if(isset($_POST["crud-files"])){
				$uses[]="use controllers\\crud\\files\\{$crudControllerName}Files;";
				$uses[]="use Ubiquity\\controllers\\crud\\CRUDFiles;";
				$classContent.=$this->_createMethod("protected", "getFiles","",": CRUDFiles","\n\t\treturn new {$crudControllerName}Files();");
				$crudFiles=$_POST["crud-views"];
				$crudFiles=explode(",", $crudFiles);
				$classFilesContent=[];
				foreach ($crudFiles as $file){
					if(isset($this->views[$file])){
						$frameworkViewname=$this->views[$file];
						$this->createCrudView($frameworkViewname,$crudControllerName, $file);
						$classFilesContent[]=$this->_createMethod("public", "getView".ucfirst($file),"","","\n\t\treturn \"".$crudControllerName."/".$file.".html\";");
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
	
	protected function createCrudView($frameworkName,$controllerName,$newName){
		$folder=ROOT . DS . "views" . DS .$controllerName;
		UFileSystem::safeMkdir($folder);
		$blocks=$this->view->getBlockNames($frameworkName);
		$content=["{% extends \"".$frameworkName."\" %}\n"];
		foreach ($blocks as $blockname){
			$content[]="{% block ".$blockname." %}\n\t{{ parent() }}\n{% endblock %}\n";
		}
		return UFileSystem::save($folder. DS .$newName.".html", implode("", $content));
	}
}
