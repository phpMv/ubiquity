<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;
use Ajax\service\JString;
use Ubiquity\controllers\Startup;
use Ajax\semantic\html\modules\checkbox\HtmlCheckbox;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\crud\CRUDHelper;
use Ubiquity\controllers\crud\CRUDMessage;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\UResponse;
use Ubiquity\controllers\rest\ResponseFormatter;
use Ajax\semantic\widgets\datatable\Pagination;

/**
 *
 * @author jc
 * @property \Ajax\JsUtils $jquery
 */
trait ModelsTrait{
	
	protected $activePage;
	
	protected $formModal="no";

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();
	
	abstract public function _getModelViewer();

	abstract public function _getAdminFiles();

	abstract protected function showSimpleMessage($content, $type, $title=null,$icon="info", $timeout=NULL, $staticName=null):HtmlMessage;

	public function showModel($model,$id=null) {
		$model=str_replace(".", "\\", $model);
		$adminRoute=$this->_getAdminFiles()->getAdminBaseRoute();
		$this->_showModel($model,$id);
		$this->_getAdminViewer()->getModelsStructureDataTable(OrmUtils::getModelMetadata($model));
		$bt=$this->jquery->semantic()->htmlButton("btYuml", "Class diagram");
		$bt->postOnClick($adminRoute. "/_showDiagram/", "{model:'" . \str_replace("\\", "|", $model) . "'}", "#modal", [ "attr" => "" ]);
		$this->jquery->exec('$("#models-tab .item").tab();', true);
		$this->jquery->getOnClick ( "#btAddNew", $adminRoute . "/newModel/" . $this->formModal, "#frm-add-update",["hasLoader"=>"internal"] );
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewShowTable(), [ "classname" => $model ]);
	}

	public function refreshTable($id=null) {
		$model=$_SESSION["model"];
		$compo= $this->_showModel($model,$id);
		$this->jquery->execAtLast('$("#table-details").html("");');
		$this->jquery->renderView("@framework/Admin/main/component.html",["compo"=>$compo]);
	}

	public function showModelClick($modelAndId) {
		$array=\explode(":", $modelAndId);
		if (\is_array($array)) {
			$table=$array[0];
			$id=$array[1];
			$this->jquery->exec("$('#menuDbs .active').removeClass('active');$('.ui.label.left.pointing.teal').removeClass('left pointing teal active');$(\"[data-model='" . $table . "']\").addClass('active');$(\"[data-model='" . $table . "']\").find('.ui.label').addClass('left pointing teal');", true);
			$this->showModel($table,$id);
			$this->jquery->execAtLast("$(\"tr[data-ajax='" . $id . "']\").click();");
			echo $this->jquery->compile();
		}
	}

	protected function _showModel($model,$id=null) {
		$_SESSION["model"]=$model;
		$datas=$this->getInstances($model,1,$id);
		$this->formModal=($this->_getModelViewer()->isModal($datas,$model))? "modal" : "no";
		return $this->_getModelViewer()->getModelDataTable($datas, $model,$this->activePage);
	}
	
	protected function getInstances($model,$page=1,$id=null){
		$this->activePage=$page;
		$recordsPerPage=$this->_getModelViewer()->recordsPerPage($model,DAO::count($model));
		if(is_numeric($recordsPerPage)){
			if(isset($id)){
				$rownum=DAO::getRownum($model, $id);
				$this->activePage=Pagination::getPageOfRow($rownum,$recordsPerPage);
			}
			return DAO::paginate($model,$this->activePage,$recordsPerPage);
		}
		return DAO::getAll($model);
	}
	
	protected function search($model,$search){
		$fields=$this->_getAdminData()->getSearchFieldNames($model);
		return CRUDHelper::search($model, $search, $fields);
	}
	
	public function refresh_(){
		$model=$_POST["_model"];
		if(isset($_POST["s"])){
			$instances=$this->search($model, $_POST["s"]);
		}else{
			$instances=$this->getInstances($model,URequest::post("p",1));
		}
		$recordsPerPage=$this->_getModelViewer()->recordsPerPage($model,DAO::count($model));
		if(isset($recordsPerPage)){
			UResponse::asJSON();
			$responseFormatter=new ResponseFormatter();
			print_r($responseFormatter->getJSONDatas($instances));
		}else{
			$this->formModal=($this->_getModelViewer()->isModal($instances,$model))? "modal" : "no";
			$compo= $this->_getModelViewer()->getModelDataTable($instances, $model)->refresh(["tbody"]);
			$this->jquery->execAtLast('$("#search-query-content").html("'.$_POST["s"].'");$("#search-query").show();$("#table-details").html("");');
			$this->jquery->renderView("@framework/Admin/main/component.html",["compo"=>$compo]);
		}
	}

	protected function _edit($instance, $modal="no") {
		$_SESSION["instance"]=$instance;
		$modal=($modal == "modal");
		$form=$this->_getModelViewer()->getForm("frmEdit", $instance);
		$this->jquery->click("#action-modal-frmEdit-0", "$('#frmEdit').form('submit');", false);
		if (!$modal) {
			$this->jquery->click("#bt-cancel", "$('#form-container').transition('drop');");
			$this->jquery->compile($this->view);
			$this->loadView($this->_getAdminFiles()->getViewEditTable(), [ "modal" => $modal ]);
		} else {
			$this->jquery->exec("$('#modal-frmEdit').modal('show');", true);
			$form=$form->asModal(\get_class($instance));
			$form->setActions([ "Okay","Cancel" ]);
			$btOkay=$form->getAction(0);
			$btOkay->addClass("green")->setValue("Validate modifications");
			$form->onHidden("$('#modal-frmEdit').remove();");
			echo $form->compile($this->jquery, $this->view);
			echo $this->jquery->compile($this->view);
		}
	}

	public function edit($modal="no", $ids="") {
		$instance=$this->getModelInstance($ids);
		$instance->_new=false;
		$this->_edit($instance, $modal);
	}

	public function newModel($modal="no") {
		$model=$_SESSION["model"];
		$instance=new $model();
		$instance->_new=true;
		$this->_edit($instance, $modal);
	}

	public function update() {
		$message=new CRUDMessage("Modifications were successfully saved", "Updating");
		$instance=@$_SESSION["instance"];
		$isNew=$instance->_new;
		$updated=CRUDHelper::update($instance, $_POST,$this->_getAdminData()->getUpdateManyToOneInForm(),$this->_getAdminData()->getUpdateManyToManyInForm());
		if($updated){
			$pk=OrmUtils::getFirstKeyValue($instance);
			$message->setType("success")->setIcon("check circle outline");
			if($isNew){
				$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute() . "/refreshTable/".$pk, "#lv", [ "jqueryDone" => "replaceWith" ]);
			}else{
				$this->jquery->setJsonToElement(OrmUtils::objectAsJSON($instance));
			}
		} else {
			$message->setMessage("An error has occurred. Can not save changes.")->setType("error")->setIcon("warning circle");
		}
		echo $this->_showSimpleMessage($message,"updateMsg");
		echo $this->jquery->compile($this->view);
	}

	private function getModelInstance($ids) {
		$model=$_SESSION['model'];
		$ids=\explode("_", $ids);
		$instance=DAO::getOne($model, $ids);
		if(isset($instance)){
			return $instance;
		}
		echo $this->showSimpleMessage("This object does not exist!", "warning","Get object","warning circle");
		echo $this->jquery->compile($this->view);
		exit(1);
	}

	public function delete($ids) {
		$instance=$this->getModelInstance($ids);
		if (method_exists($instance, "__toString"))
			$instanceString=$instance . "";
		else
			$instanceString=get_class($instance);
		if (sizeof($_POST) > 0) {
			if (DAO::remove($instance)) {
				$message=$this->showSimpleMessage("Deletion of `<b>" . $instanceString . "</b>`", "info","Deletion", "info", 4000);
				$this->jquery->exec("$('tr[data-ajax={$ids}]').remove();", true);
			} else {
				$message=$this->showSimpleMessage("Can not delete `" . $instanceString . "`", "warning","Error", "warning");
			}
		} else {
			$message=$this->showConfMessage("Do you confirm the deletion of `<b>" . $instanceString . "</b>`?", "error","Remove confirmation", $this->_getAdminFiles()->getAdminBaseRoute() . "/delete/{$ids}", "#table-messages", $ids);
		}
		echo $message;
		echo $this->jquery->compile($this->view);
	}

	private function getFKMethods($model) {
		$reflection=new \ReflectionClass($model);
		$publicMethods=$reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		$result=[ ];
		foreach ( $publicMethods as $method ) {
			$methodName=$method->getName();
			if (JString::startswith($methodName, "get")) {
				$attributeName=lcfirst(JString::replaceAtFirst($methodName, "get", ""));
				if (!property_exists($model, $attributeName))
					$result[]=$methodName;
			}
		}
		return $result;
	}

	public function showDetail($ids) {
		$instance=$this->getModelInstance($ids);
		$viewer=$this->_getModelViewer();
		$hasElements=false;
		$model=$_SESSION['model'];
		$fkInstances=CRUDHelper::getFKIntances($instance, $model);	
		$semantic=$this->jquery->semantic();
		$grid=$semantic->htmlGrid("detail");
		if (sizeof($fkInstances) > 0) {
			$wide=intval(16 / sizeof($fkInstances));
			if ($wide < 4)
				$wide=4;
			foreach ( $fkInstances as $member=>$fkInstanceArray ) {
				$element=$viewer->getFkMemberElementDetails($member,$fkInstanceArray["objectFK"],$fkInstanceArray["fkClass"],$fkInstanceArray["fkTable"]);
				if (isset($element)) {
					$grid->addCol($wide)->setContent($element);
					$hasElements=true;
				}
			}
			if ($hasElements)
				echo $grid;
			$this->jquery->getOnClick(".showTable", $this->_getAdminFiles()->getAdminBaseRoute() . "/showModelClick", "#divTable", [ "attr" => "data-ajax","ajaxTransition" => "random" ]);
			echo $this->jquery->compile($this->view);
		}

	}

	protected function getModelsNS() {
		return Startup::getConfig()["mvcNS"]["models"];
	}

	private function _getCks($array) {
		$result=[ ];
		foreach ( $array as $dataAjax => $caption ) {
			$result[]=$this->_getCk($caption, $dataAjax);
		}
		return $result;
	}

	private function _getCk($caption, $dataAjax) {
		$ck=new HtmlCheckbox("ck-" . $dataAjax, $caption, "1");
		$ck->setProperty("name", "ck[]");
		$ck->setProperty("data-ajax", $dataAjax);
		return $ck;
	}
}
