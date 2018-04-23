<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\JsUtils;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;
use Ajax\service\JString;
use Ubiquity\controllers\Startup;
use Ajax\semantic\html\modules\checkbox\HtmlCheckbox;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\crud\CRUDHelper;

/**
 *
 * @author jc
 * @property JsUtils $jquery
 */
trait ModelsTrait{
	
	protected $formModal="no";

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();
	
	abstract public function _getAdminModelViewer();

	abstract public function _getAdminFiles();

	abstract protected function showSimpleMessage($content, $type, $icon="info", $timeout=NULL, $staticName=null):HtmlMessage;

	public function showTable($table) {
		$model=$this->getModelsNS() . "\\" . ucfirst($table);
		$adminRoute=$this->_getAdminFiles()->getAdminBaseRoute();
		$this->_showModel($model);
		$this->_getAdminViewer()->getModelsStructureDataTable(OrmUtils::getModelMetadata($model));
		$bt=$this->jquery->semantic()->htmlButton("btYuml", "Class diagram");
		$bt->postOnClick($adminRoute. "/_showDiagram/", "{model:'" . \str_replace("\\", "|", $model) . "'}", "#modal", [ "attr" => "" ]);
		$this->jquery->exec('$("#models-tab .item").tab();', true);
		$this->jquery->getOnClick ( "#btAddNew", $adminRoute . "/newModel/" . $this->formModal, "#frm-add-update",["hasLoader"=>"internal"] );
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewShowTable(), [ "classname" => $model ]);
	}

	public function refreshTable() {
		$model=$_SESSION["model"];
		echo $this->_showModel($model);
		echo $this->jquery->compile($this->view);
	}

	public function showTableClick($tableAndId) {
		$array=\explode(".", $tableAndId);
		if (\is_array($array)) {
			$table=$array[0];
			$id=$array[1];
			$this->jquery->exec("$('#menuDbs .active').removeClass('active');$('.ui.label.left.pointing.teal').removeClass('left pointing teal active');$(\"[data-ajax='" . $table . "']\").addClass('active');$(\"[data-ajax='" . $table . "']\").find('.ui.label').addClass('left pointing teal');", true);
			$this->showTable($table);
			$this->jquery->exec("$(\"tr[data-ajax='" . $id . "']\").click();", true);
			echo $this->jquery->compile();
		}
	}

	protected function _showModel($model) {
		$_SESSION["model"]=$model;
		$datas=DAO::getAll($model);
		$this->formModal=($this->_getAdminModelViewer()->isModal($datas,$model))? "modal" : "no";
		return $this->_getAdminModelViewer()->getModelDataTable($datas, $model);
	}

	protected function _edit($instance, $modal="no") {
		$_SESSION["instance"]=$instance;
		$modal=($modal == "modal");
		$form=$this->_getAdminModelViewer()->getForm("frmEdit", $instance);
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
		$message=$this->jquery->semantic()->htmlMessage("msgUpdate", "Modifications were successfully saved", "info");
		$instance=@$_SESSION["instance"];
		$updated=CRUDHelper::update($instance, $_POST,$this->_getAdminData()->getUpdateManyToOneInForm(),$this->_getAdminData()->getUpdateManyToManyInForm());
		if($updated){
			$message->setStyle("success")->setIcon("checkmark");
			$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute() . "/refreshTable", "#lv", [ "jqueryDone" => "replaceWith" ]);
		} else {
			$message->setContent("An error has occurred. Can not save changes.")->setStyle("error")->setIcon("warning circle");
		}
		echo $message;
		echo $this->jquery->compile($this->view);
	}

	private function getModelInstance($ids) {
		$model=$_SESSION['model'];
		$ids=\explode("_", $ids);
		$instance=DAO::getOne($model, $ids);
		if(isset($instance)){
			return $instance;
		}
		echo $this->showSimpleMessage("This object does not exist!", "warning","warning circle");
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
				$message=$this->showSimpleMessage("Deletion of `<b>" . $instanceString . "</b>`", "info", "info", 4000);
				$this->jquery->exec("$('tr[data-ajax={$ids}]').remove();", true);
			} else {
				$message=$this->showSimpleMessage("Can not delete `" . $instanceString . "`", "warning", "warning");
			}
		} else {
			$message=$this->showConfMessage("Do you confirm the deletion of `<b>" . $instanceString . "</b>`?", "error", $this->_getAdminFiles()->getAdminBaseRoute() . "/delete/{$ids}", "#table-messages", $ids);
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
		$viewer=$this->_getAdminModelViewer();
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
				$element=$viewer->getFkMemberElement($member,$fkInstanceArray["objectFK"],$fkInstanceArray["fkClass"],$fkInstanceArray["fkTable"]);
				if (isset($element)) {
					$grid->addCol($wide)->setContent($element);
					$hasElements=true;
				}
			}
			if ($hasElements)
				echo $grid;
			$this->jquery->getOnClick(".showTable", $this->_getAdminFiles()->getAdminBaseRoute() . "/showTableClick", "#divTable", [ "attr" => "data-ajax","ajaxTransition" => "random" ]);
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
