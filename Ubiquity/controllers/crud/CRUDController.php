<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\orm\DAO;
use Ubiquity\controllers\ControllerBase;
use Ubiquity\controllers\admin\interfaces\HasModelViewerInterface;
use Ubiquity\controllers\admin\viewers\ModelViewer;
use Ubiquity\controllers\semantic\MessagesTrait;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\UResponse;
use Ubiquity\controllers\rest\ResponseFormatter;
use Ajax\semantic\widgets\datatable\Pagination;
use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\base\UString;

abstract class CRUDController extends ControllerBase implements HasModelViewerInterface{
	use MessagesTrait;
	protected $model;
	protected $modelViewer;
	protected $events;
	protected $crudFiles;
	protected $adminDatas;
	protected $activePage;
	
	/**
	 * Default page : list all objects
	 */
	public function index() {
		$objects=$this->getInstances();
		$modal=($this->_getModelViewer()->isModal($objects,$this->model))?"modal":"no";
		$this->_getModelViewer()->getModelDataTable($objects, $this->model);
		$this->jquery->getOnClick ( "#btAddNew", $this->_getBaseRoute() . "/newModel/" . $modal, "#frm-add-update",["hasLoader"=>"internal"] );
		$this->crudLoadView($this->_getFiles()->getViewIndex(), [ "classname" => $this->model ]);		
	}
	
	protected function getInstances($page=1,$id=null){
		$this->activePage=$page;
		$model=$this->model;
		$condition=$this->_getInstancesFilter($model);
		$recordsPerPage=$this->_getModelViewer()->recordsPerPage($model,DAO::count($model,$condition));
		if(is_numeric($recordsPerPage)){
			if(isset($id)){
				$rownum=DAO::getRownum($model, $id);
				$this->activePage=Pagination::getPageOfRow($rownum,$recordsPerPage);
			}
			return DAO::paginate($model,$this->activePage,$recordsPerPage);
		}
		return DAO::getAll($model,$condition);
	}
	
	public function _getInstancesFilter($model){
		return "1=1";
	}
	
	protected function search($model,$search){
		$fields=$this->_getAdminData()->getSearchFieldNames($model);
		$condition=$this->_getInstancesFilter($model);
		return CRUDHelper::search($model, $search, $fields,$condition);
	}
	
	public function refresh_(){
		$model=$this->model;
		if(isset($_POST["s"])){
			$instances=$this->search($model, $_POST["s"]);
		}else{
			$instances=$this->getInstances(URequest::post("p",1));
		}
		$recordsPerPage=$this->_getModelViewer()->recordsPerPage($model,DAO::count($model,$this->_getInstancesFilter($model)));
		if(isset($recordsPerPage)){
			UResponse::asJSON();
			$responseFormatter=new ResponseFormatter();
			print_r($responseFormatter->getJSONDatas($instances));
		}else{
			$this->formModal=($this->_getModelViewer()->isModal($instances,$model))? "modal" : "no";
			$compo= $this->_getModelViewer()->getModelDataTable($instances, $model)->refresh(["tbody"]);
			$this->jquery->execAtLast('$("#search-query-content").html("'.$_POST["s"].'");$("#search-query").show();$("#table-details").html("");');
			$this->jquery->renderView("@framework/main/component.html",["compo"=>$compo]);
		}
	}
	
	/**
	 * Edits an instance
	 * @param string $modal Accept "no" or "modal" for a modal dialog
	 * @param string $ids the primary value(s)
	 */
	public function edit($modal="no", $ids="") {
		$instance=$this->getModelInstance($ids);
		$instance->_new=false;
		$this->_edit($instance, $modal);
	}
	/**
	 * Adds a new instance and edits it
	 * @param string $modal Accept "no" or "modal" for a modal dialog
	 */
	public function newModel($modal="no") {
		$model=$this->model;
		$instance=new $model();
		$instance->_new=true;
		$this->_edit($instance, $modal);
	}
	
	/**
	 * Displays an instance
	 * @param string $modal
	 * @param string $ids
	 */
	public function display($modal="no",$ids=""){
		$instance=$this->getModelInstance($ids);
		$key=OrmUtils::getFirstKeyValue($instance);
		$this->jquery->execOn("click","._close",'$("#table-details").html("");$("#dataTable").show();');
		$this->jquery->getOnClick("._edit", $this->_getBaseRoute()."/edit/".$modal."/".$key,"#frm-add-update");
		$this->jquery->getOnClick("._delete", $this->_getBaseRoute()."/delete/".$key,"#table-messages");
		
		$this->_getModelViewer()->getModelDataElement($instance, $this->model,$modal);
		$this->jquery->renderView($this->_getFiles()->getViewDisplay(), [ "classname" => $this->model,"instance"=>$instance,"pk"=>$key ]);
	}
	
	protected function _edit($instance, $modal="no") {
		$_SESSION["instance"]=$instance;
		$modal=($modal == "modal");
		$form=$this->_getModelViewer()->getForm("frmEdit", $instance);
		$this->jquery->click("#action-modal-frmEdit-0", "$('#frmEdit').form('submit');", false);
		if (!$modal) {
			$this->jquery->click("#bt-cancel", "$('#form-container').transition('drop');");
			$this->jquery->compile($this->view);
			$this->loadView($this->_getFiles()->getViewForm(), [ "modal" => $modal ]);
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
	
	protected function _showModel($id=null) {
		$model=$this->model;
		$datas=$this->getInstances(1,$id);
		$this->formModal=($this->_getModelViewer()->isModal($datas,$model))? "modal" : "no";
		return $this->_getModelViewer()->getModelDataTable($datas, $model,$this->activePage);
	}
	
	/**
	 * Deletes an instance
	 * @param mixed $ids
	 */
	public function delete($ids) {
		$instance=$this->getModelInstance($ids);
		if (method_exists($instance, "__toString"))
			$instanceString=$instance . "";
		else
			$instanceString=get_class($instance);
		if (sizeof($_POST) > 0) {
			try{
				if (DAO::remove($instance)) {
					$message=new CRUDMessage("Deletion of `<b>" . $instanceString . "</b>`","Deletion","info","info circle",4000);
					$message=$this->_getEvents()->onSuccessDeleteMessage($message);
					$this->jquery->exec("$('._element[data-ajax={$ids}]').remove();", true);
				} else {
					$message=new CRUDMessage("Can not delete `" . $instanceString . "`","Deletion","warning","warning circle");
					$message=$this->_getEvents()->onErrorDeleteMessage($message);
				}
			}catch (\Exception $e){
				$message=new CRUDMessage("Exception : can not delete `" . $instanceString . "`","Exception", "warning", "warning");
				$message=$this->_getEvents()->onErrorDeleteMessage($message);
			}
			$message=$this->_showSimpleMessage($message);
		} else {
			$message=new CRUDMessage("Do you confirm the deletion of `<b>" . $instanceString . "</b>`?", "Remove confirmation","error");
			$this->_getEvents()->onConfDeleteMessage($message);
			$message=$this->_showConfMessage($message, $this->_getBaseRoute() . "/delete/{$ids}", "#table-messages", $ids);
		}
		echo $message;
		echo $this->jquery->compile($this->view);
	}
	

	
	public function refreshTable($id=null) {
		$compo= $this->_showModel($id);
		$this->jquery->execAtLast('$("#table-details").html("");');
		$this->jquery->renderView("@framework/main/component.html",["compo"=>$compo]);	
	}
	
	/**
	 * Updates an instance from the data posted in a form
	 */
	public function update() {
		$message=new CRUDMessage("Modifications were successfully saved", "Updating");
		$instance=@$_SESSION["instance"];
		$isNew=$instance->_new;
		try{
			$updated=CRUDHelper::update($instance, $_POST,$this->_getAdminData()->getUpdateManyToOneInForm(),$this->_getAdminData()->getUpdateManyToManyInForm());
			if($updated){
				$message->setType("success")->setIcon("check circle outline");
				$message=$this->_getEvents()->onSuccessUpdateMessage($message);
				$this->refreshInstance($instance,$isNew);
			} else {
				$message->setMessage("An error has occurred. Can not save changes.")->setType("error")->setIcon("warning circle");
				$message=$this->_getEvents()->onErrorUpdateMessage($message);
			}
		}catch(\Exception $e){
			if (method_exists($instance, "__toString"))
				$instanceString=$instance . "";
			else
				$instanceString=get_class($instance);
			$message=new CRUDMessage("Exception : can not update `" . $instanceString . "`","Exception", "warning", "warning");
			$message=$this->_getEvents()->onErrorUpdateMessage($message);
		}
		echo $this->_showSimpleMessage($message,"updateMsg");
		echo $this->jquery->compile($this->view);
	}
	
	protected function refreshInstance($instance,$isNew){
		if($this->_getAdminData()->refreshPartialInstance() && !$isNew){
			$this->jquery->setJsonToElement(OrmUtils::objectAsJSON($instance));
		}else{
			$pk=OrmUtils::getFirstKeyValue($instance);
			$this->jquery->get($this->_getBaseRoute() . "/refreshTable/".$pk, "#lv", [ "jqueryDone" => "replaceWith" ]);
		}
	}
	
	/**
	 * Shows associated members with foreign keys
	 * @param mixed $ids
	 */
	public function showDetail($ids) {
		$instance=$this->getModelInstance($ids);
		$viewer=$this->_getModelViewer();
		$hasElements=false;
		$model=$this->model;
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
					$url=$this->_getEvents()->onDetailClickURL($this->model);
				if(UString::isNotNull($url)){
					$this->detailClick($url);
				}
				echo $this->jquery->compile($this->view);
		}

	}
	
	public function detailClick($url) {
		$this->jquery->postOnClick(".showTable", $this->_getBaseRoute() . "/".$url,"{}", "#divTable", [ "attr" => "data-ajax","ajaxTransition" => "random" ]);
	}
	
	private function getModelInstance($ids) {
		$ids=\explode("_", $ids);
		$instance=DAO::getOne($this->model, $ids);
		if(isset($instance)){
			return $instance;
		}
		$message=new CRUDMessage("This object does not exist!","Get object","warning","warning circle");
		$message=$this->_getEvents()->onNotFoundMessage($message);
		echo $this->_showSimpleMessage($message);
		echo $this->jquery->compile($this->view);
		exit(1);
	}
	
	/**
	 * To override for defining a new adminData
	 * @return CRUDDatas
	 */
	protected function getAdminData ():CRUDDatas{
		return new CRUDDatas();
	}
	
	public function _getAdminData ():CRUDDatas{
		return $this->getSingleton($this->modelViewer,"getAdminData");
	}
	
	/**
	 * To override for defining a new ModelViewer
	 * @return ModelViewer
	 */
	protected function getModelViewer ():ModelViewer{
		return new ModelViewer($this);
	}
	
	private function _getModelViewer():ModelViewer{
		return $this->getSingleton($this->modelViewer,"getModelViewer");
	}
	
	/**
	 * To override for changing view files
	 * @return CRUDFiles
	 */
	protected function getFiles ():CRUDFiles{
		return new CRUDFiles();
	}
	
	private function _getFiles():CRUDFiles{
		return $this->getSingleton($this->crudFiles,"getFiles");
	}
	
	/**
	 * To override for changing events
	 * @return CRUDEvents
	 */
	protected function getEvents ():CRUDEvents{
		return new CRUDEvents();
	}
	
	private function _getEvents():CRUDEvents{
		return $this->getSingleton($this->events,"getEvents");
	}
	
	private function getSingleton($value, $method) {
		if (! isset ( $value )) {
			$value = $this->$method ();
		}
		return $value;
	}
	
	private function crudLoadView($viewName,$vars=[]){
		if(!URequest::isAjax()){
			$files=$this->_getFiles();
			$mainTemplate=$files->getBaseTemplate();
			if(isset($mainTemplate)){
				$vars["_viewname"]=$viewName;
				$vars["_base"]=$mainTemplate;
				$this->jquery->renderView($files->getViewBaseTemplate(),$vars);
			}else{
				$vars["hasScript"]=true;
				$this->jquery->renderView($viewName,$vars);
			}
		}else{
			$vars["hasScript"]=true;
			$this->jquery->renderView($viewName,$vars);
		}
	}
}
