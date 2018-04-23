<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\orm\DAO;
use Ubiquity\controllers\ControllerBase;
use Ubiquity\controllers\admin\interfaces\HasModelViewerInterface;
use Ubiquity\controllers\admin\UbiquityMyAdminData;
use Ubiquity\controllers\admin\viewers\ModelViewer;
use Ubiquity\controllers\semantic\MessagesTrait;

abstract class CRUDController extends ControllerBase implements HasModelViewerInterface{
	use MessagesTrait;
	protected $model;
	protected $modelViewer;
	protected $crudFiles;
	
	/**
	 * Default page : list all objects
	 */
	public function index() {
		$objects=DAO::getAll($this->model);
		$modal=($this->_getModelViewer()->isModal($objects,$this->model))?"modal":"no";
		$this->_getModelViewer()->getModelDataTable($objects, $this->model);
		$this->jquery->getOnClick ( "#btAddNew", $this->_getBaseRoute() . "/newModel/" . $modal, "#frm-add-update",["hasLoader"=>"internal"] );
		$this->jquery->compile($this->view);
		$this->loadView($this->_getFiles()->getViewShowTable(), [ "classname" => $this->model ]);
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
	
	protected function _edit($instance, $modal="no") {
		$_SESSION["instance"]=$instance;
		$modal=($modal == "modal");
		$form=$this->_getModelViewer()->getForm("frmEdit", $instance);
		$this->jquery->click("#action-modal-frmEdit-0", "$('#frmEdit').form('submit');", false);
		if (!$modal) {
			$this->jquery->click("#bt-cancel", "$('#form-container').transition('drop');");
			$this->jquery->compile($this->view);
			$this->loadView($this->_getFiles()->getViewEditTable(), [ "modal" => $modal ]);
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
	
	protected function _showModel() {
		$model=$this->model;
		$datas=DAO::getAll($model);
		return $this->_getModelViewer()->getModelDataTable($datas, $model);
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
					$message=$this->showSimpleMessage("Deletion of `<b>" . $instanceString . "</b>`", "info", "info", 4000);
					$this->jquery->exec("$('tr[data-ajax={$ids}]').remove();", true);
				} else {
					$message=$this->showSimpleMessage("Can not delete `" . $instanceString . "`", "warning", "warning");
				}
			}catch (\Exception $e){
				$message=$this->showSimpleMessage("Exception : can not delete `" . $instanceString . "`", "warning", "warning");
			}
		} else {
			$message=$this->showConfMessage("Do you confirm the deletion of `<b>" . $instanceString . "</b>`?", "error", $this->_getBaseRoute() . "/delete/{$ids}", "#table-messages", $ids);
		}
		echo $message;
		echo $this->jquery->compile($this->view);
	}
	
	public function refreshTable() {
		echo $this->_showModel();
		echo $this->jquery->compile($this->view);
	}
	
	/**
	 * Updates an instance from the data posted in a form
	 */
	public function update() {
		$message=$this->jquery->semantic()->htmlMessage("msgUpdate", "Modifications were successfully saved", "info");
		$instance=@$_SESSION["instance"];
		$updated=CRUDHelper::update($instance, $_POST,$this->_getAdminData()->getUpdateManyToOneInForm(),$this->_getAdminData()->getUpdateManyToManyInForm());
		if($updated){
			$message->setStyle("success")->setIcon("checkmark");
			$this->jquery->get($this->_getBaseRoute() . "/refreshTable", "#lv", [ "jqueryDone" => "replaceWith" ]);
		} else {
			$message->setContent("An error has occurred. Can not save changes.")->setStyle("error")->setIcon("warning circle");
		}
		echo $message;
		echo $this->jquery->compile($this->view);
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
					$element=$viewer->getFkMemberElement($member,$fkInstanceArray["objectFK"],$fkInstanceArray["fkClass"],$fkInstanceArray["fkTable"]);
					if (isset($element)) {
						$grid->addCol($wide)->setContent($element);
						$hasElements=true;
					}
				}
				if ($hasElements)
					echo $grid;
				$this->jquery->getOnClick(".showTable", $this->_getBaseRoute() . "/showTableClick", "#divTable", [ "attr" => "data-ajax","ajaxTransition" => "random" ]);
				echo $this->jquery->compile($this->view);
		}

	}
	
	private function getModelInstance($ids) {
		$ids=\explode("_", $ids);
		$instance=DAO::getOne($this->model, $ids);
		if(isset($instance)){
			return $instance;
		}
		echo $this->showSimpleMessage("This object does not exist!", "warning","warning circle");
		echo $this->jquery->compile($this->view);
		exit(1);
	}
	
	public function _getAdminData ():UbiquityMyAdminData{
		return new UbiquityMyAdminData();
	}
	
	/**
	 * To override for defining a new ModelViewer
	 * @return ModelViewer
	 */
	protected function getModelViewer ():ModelViewer{
		return new ModelViewer($this);
	}
	
	private function _getModelViewer():modelViewer{
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
	
	private function getSingleton($value, $method) {
		if (! isset ( $value )) {
			$value = $this->$method ();
		}
		return $value;
	}
}

