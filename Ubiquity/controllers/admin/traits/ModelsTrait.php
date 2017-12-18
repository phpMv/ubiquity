<?php
namespace Ubiquity\controllers\admin\traits;

use Ajax\JsUtils;
use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\RequestUtils;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\orm\DAO;
use Ajax\service\JString;
use Ajax\semantic\html\elements\HtmlHeader;
use Ubiquity\controllers\Startup;
use Ajax\semantic\html\modules\checkbox\HtmlCheckbox;
use Ubiquity\cache\database\DbCache;

/**
 * @author jc
 * @property JsUtils $jquery
 */
trait ModelsTrait{
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	abstract public function _getAdminFiles();
	abstract protected function showSimpleMessage($content,$type,$icon="info",$timeout=NULL,$staticName=null);

	public function showTable($table){
		$this->_showTable($table);
		$model=$this->getModelsNS()."\\".ucfirst($table);
		$this->_getAdminViewer()->getModelsStructureDataTable(OrmUtils::getModelMetadata($model));
		$bt=$this->jquery->semantic()->htmlButton("btYuml","Class diagram");
		$bt->postOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/_showDiagram/","{model:'".\str_replace("\\", "|", $model)."'}","#modal",["attr"=>""]);
		$this->jquery->exec('$("#models-tab .item").tab();',true);
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewShowTable(),["classname"=>$model]);
	}

	public function refreshTable(){
		$table=$_SESSION["table"];
		echo $this->_showTable($table);
		echo $this->jquery->compile($this->view);
	}

	public function showTableClick($tableAndId){
		$array=\explode(".", $tableAndId);
		if(\is_array($array)){
			$table=$array[0];
			$id=$array[1];
			$this->jquery->exec("$('#menuDbs .active').removeClass('active');$('.ui.label.left.pointing.teal').removeClass('left pointing teal active');$(\"[data-ajax='".$table."']\").addClass('active');$(\"[data-ajax='".$table."']\").find('.ui.label').addClass('left pointing teal');",true);
			$this->showTable($table);
			$this->jquery->exec("$(\"tr[data-ajax='".$id."']\").click();",true);
			echo $this->jquery->compile();
		}
	}

	protected function _showTable($table){
		$adminRoute=$this->_getAdminFiles()->getAdminBaseRoute();
		$_SESSION["table"]= $table;
		$semantic=$this->jquery->semantic();
		$model=$this->getModelsNS()."\\".ucfirst($table);

		$datas=DAO::getAll($model);
		$modal=($this->_getAdminViewer()->isModal($datas, $model)?"modal":"no");
		$lv=$semantic->dataTable("lv", $model, $datas);
		$attributes=$this->getFieldNames($model);

		$lv->setCaptions($this->_getAdminViewer()->getCaptions($attributes, $model));
		$lv->setFields($attributes);
		$lv->onPreCompile(function() use ($attributes,&$lv){
			$lv->getHtmlComponent()->colRight(\count($attributes));
		});

			$lv->setIdentifierFunction($this->getIdentifierFunction($model));
			$lv->getOnRow("click", $adminRoute."/showDetail","#table-details",["attr"=>"data-ajax"]);
			$lv->setUrls(["delete"=>$adminRoute."/delete","edit"=>$adminRoute."/edit/".$modal]);
			$lv->setTargetSelector(["delete"=>"#table-messages","edit"=>"#table-details"]);
			$lv->addClass("small very compact");
			$lv->addEditDeleteButtons(false,["ajaxTransition"=>"random"],function($bt){$bt->addClass("circular");},function($bt){$bt->addClass("circular");});
			$lv->setActiveRowSelector("error");
			$this->jquery->getOnClick("#btAddNew", $adminRoute."/newModel/".$modal,"#table-details");
			$this->jquery->click("_.edit","console.log($(this).closest('.ui.button'));");
			return $lv;
	}

	protected function _edit($instance,$modal="no"){
		$_SESSION["instance"]=$instance;
		$modal=($modal=="modal");
		$form=$this->_getAdminViewer()->getForm("frmEdit",$instance);
		$this->jquery->click("#action-modal-frmEdit-0","$('#frmEdit').form('submit');",false);
		if(!$modal){
			$this->jquery->click("#bt-cancel","$('#form-container').transition('drop');");
			$this->jquery->compile($this->view);
			$this->loadView($this->_getAdminFiles()->getViewEditTable(),["modal"=>$modal]);
		}else{
			$this->jquery->exec("$('#modal-frmEdit').modal('show');",true);
			$form=$form->asModal(\get_class($instance));
			$form->setActions(["Okay","Cancel"]);
			$btOkay=$form->getAction(0);
			$btOkay->addClass("green")->setValue("Validate modifications");
			$form->onHidden("$('#modal-frmEdit').remove();");
			echo $form->compile($this->jquery,$this->view);
			echo $this->jquery->compile($this->view);
		}
	}

	public function edit($modal="no",$ids=""){
		$instance=$this->getModelInstance($ids);
		$instance->_new=false;
		$this->_edit($instance,$modal);
	}

	public function newModel($modal="no"){
		$model=$this->getModelsNS()."\\".ucfirst($_SESSION["table"]);
		$instance=new $model();
		$instance->_new=true;
		$this->_edit($instance,$modal);
	}

	public function update(){
		$message=$this->jquery->semantic()->htmlMessage("msgUpdate","Modifications were successfully saved","info");
		$instance=@$_SESSION["instance"];
		$className=\get_class($instance);
		$relations = OrmUtils::getManyToOneFields($className);
		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ($fieldTypes as $property=>$type){
			if($type=="tinyint(1)"){
				if(isset($_POST[$property])){
					$_POST[$property]=1;
				}else{
					$_POST[$property]=0;
				}
			}
		}
		RequestUtils::setValuesToObject($instance,$_POST);
		foreach ($relations as $member){
			if($this->_getAdminData()->getUpdateManyToOneInForm()){
				$joinColumn=OrmUtils::getAnnotationInfoMember($className, "#joinColumn", $member);
				if($joinColumn){
					$fkClass=$joinColumn["className"];
					$fkField=$joinColumn["name"];
					if(isset($_POST[$fkField])){
						$fkObject=DAO::getOne($fkClass, $_POST["$fkField"]);
						Reflexion::setMemberValue($instance, $member, $fkObject);
					}
				}
			}
		}
		if(isset($instance)){
			if($instance->_new){
				$update=DAO::insert($instance);
			}else{
				$update=DAO::update($instance);
				if(DbCache::$active){
					//TODO update dbCache
				}
			}
			if($update){
				if($this->_getAdminData()->getUpdateManyToManyInForm()){
					$relations = OrmUtils::getManyToManyFields($className);
					foreach ($relations as $member){
						if(($annot=OrmUtils::getAnnotationInfoMember($className, "#manyToMany",$member))!==false){
							$newField=$member."Ids";
							$fkClass=$annot["targetEntity"];
							$fkObjects=DAO::getAll($fkClass,$this->getMultiWhere($_POST[$newField], $className));
							if(Reflexion::setMemberValue($instance, $member, $fkObjects)){
								DAO::insertOrUpdateManyToMany($instance, $member);
							}
						}
					}
				}
				$message->setStyle("success")->setIcon("checkmark");
				$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute()."/refreshTable","#lv",["jqueryDone"=>"replaceWith"]);
			}else{
				$message->setContent("An error has occurred. Can not save changes.")->setStyle("error")->setIcon("warning circle");
			}
			echo $message;
			echo $this->jquery->compile($this->view);
		}
	}

	private function getPks($model){
		$instance = new $model();
		return OrmUtils::getKeyFields($instance);
	}

	private function getMultiWhere($ids,$class){
		$pk=OrmUtils::getFirstKey($class);
		$ids=explode(",", $ids);
		if(sizeof($ids)<1)
			return "";
			$strs=[];
			$idCount=\sizeof($ids);
			for($i=0;$i<$idCount;$i++){
				$strs[]=$pk."='".$ids[$i]."'";
			}
			return implode(" OR ", $strs);
	}

	private function getOneWhere($ids,$table){
		$ids=explode("_", $ids);
		if(sizeof($ids)<1)
			return "";
			$pks=$this->getPks(ucfirst($table));
			$strs=[];
			$idCount=\sizeof($ids);
			for($i=0;$i<$idCount;$i++){
				$strs[]=$pks[$i]."='".$ids[$i]."'";
			}
			return implode(" AND ", $strs);
	}

	private function getModelInstance($ids){
		$table=$_SESSION['table'];
		$model=$this->getModelsNS()."\\".ucfirst($table);
		$ids=\explode("_", $ids);
		return DAO::getOne($model,$ids);
	}

	public function delete($ids){
		$instance=$this->getModelInstance($ids);
		if(method_exists($instance, "__toString"))
			$instanceString=$instance."";
			else
				$instanceString=get_class($instance);
				if(sizeof($_POST)>0){
					if(DAO::remove($instance)){
						$message=$this->showSimpleMessage("Suppression de `".$instanceString."`", "info","info",4000);
						$this->jquery->exec("$('tr[data-ajax={$ids}]').remove();",true);
					}else{
						$message=$this->showSimpleMessage("Impossible de supprimer `".$instanceString."`", "warning","warning");
					}
				}else{
					$message=$this->showConfMessage("Confirmez la suppression de `".$instanceString."`?", "", $this->_getAdminFiles()->getAdminBaseRoute()."/delete/{$ids}", "#table-messages", $ids);
				}
				echo $message;
				echo $this->jquery->compile($this->view);
	}

	private function getFKMethods($model){
		$reflection=new \ReflectionClass($model);
		$publicMethods=$reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		$result=[];
		foreach ($publicMethods as $method){
			$methodName=$method->getName();
			if(JString::startswith($methodName, "get")){
				$attributeName=lcfirst(JString::replaceAtFirst($methodName, "get", ""));
				if(!property_exists($model, $attributeName))
					$result[]=$methodName;
			}
		}
		return $result;
	}

	public function showDetail($ids){
		$viewer=$this->_getAdminViewer();
		$hasElements=false;
		$instance=$this->getModelInstance($ids);
		$table=$_SESSION['table'];
		$model=$this->getModelsNS()."\\".ucfirst($table);
		$relations = OrmUtils::getFieldsInRelations($model);
		$semantic=$this->jquery->semantic();
		$grid=$semantic->htmlGrid("detail");
		if(sizeof($relations)>0){
			$wide=intval(16/sizeof($relations));
			if($wide<4)
				$wide=4;
				foreach ($relations as $member){
					if(($annot=OrmUtils::getAnnotationInfoMember($model, "#oneToMany",$member))!==false){
						$objectFK=DAO::getOneToMany($instance, $member);
						$fkClass=$annot["className"];
					}elseif(($annot=OrmUtils::getAnnotationInfoMember($model, "#manyToMany",$member))!==false){
						$objectFK=DAO::getManyToMany($instance, $member);
						$fkClass=$annot["targetEntity"];
					}else{
						$objectFK=Reflexion::getMemberValue($instance, $member);
						if(isset($objectFK))
							$fkClass=\get_class($objectFK);
					}
					if(isset($fkClass)){
						$fkTable=OrmUtils::getTableName($fkClass);
						$memberFK=$member;

						$header=new HtmlHeader("",4,$memberFK,"content");
						if(is_array($objectFK) || $objectFK instanceof \Traversable){
							$header=$viewer->getFkHeaderList($memberFK, $fkClass, $objectFK);
							$element=$viewer->getFkList($memberFK, $fkClass, $objectFK);
							foreach ($objectFK as $item){
								if(method_exists($item, "__toString")){
									$id=($this->getIdentifierFunction($fkClass))(0,$item);
									$item=$element->addItem($item."");
									$item->setProperty("data-ajax", $fkTable.".".$id);
									$item->addClass("showTable");
									$hasElements=true;
									$this->_getAdminViewer()->displayFkElementList($item, $memberFK, $fkClass, $item);
								}
							}
						}else{
							if(method_exists($objectFK, "__toString")){
								$header=$viewer->getFkHeaderElement($memberFK, $fkClass, $objectFK);
								$id=($this->getIdentifierFunction($fkClass))(0,$objectFK);
								$element=$viewer->getFkElement($memberFK, $fkClass, $objectFK);
								$element->setProperty("data-ajax", $fkTable.".".$id)->addClass("showTable");
							}
						}
						if(isset($element)){
							$grid->addCol($wide)->setContent($header.$element);
							$hasElements=true;
						}
					}
				}
				if($hasElements)
					echo $grid;
					$this->jquery->getOnClick(".showTable", $this->_getAdminFiles()->getAdminBaseRoute()."/showTableClick","#divTable",["attr"=>"data-ajax","ajaxTransition"=>"random"]);
					echo $this->jquery->compile($this->view);
		}
	}

	protected function getModelsNS(){
		return Startup::getConfig()["mvcNS"]["models"];
	}

	private function _getCks($array){
		$result=[];
		foreach ($array as $dataAjax=>$caption){
			$result[]=$this->_getCk($caption, $dataAjax);
		}
		return $result;
	}
	private function _getCk($caption,$dataAjax){
		$ck=new HtmlCheckbox("ck-".$dataAjax,$caption,"1");
		$ck->setProperty("name", "ck[]");
		$ck->setProperty("data-ajax", $dataAjax);
		return $ck;
	}
}
