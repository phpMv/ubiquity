<?php
namespace micro\controllers\admin;
use Ajax\service\JString;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\semantic\html\elements\HtmlButton;
use micro\orm\DAO;
use micro\orm\OrmUtils;
use micro\orm\parser\Reflexion;
use micro\controllers\Startup;
use micro\controllers\Autoloader;
use micro\controllers\admin\UbiquityMyAdminData;
use controllers\ControllerBase;
use micro\utils\RequestUtils;

class UbiquityMyAdminBaseController extends ControllerBase{
	/**
	 * @var UbiquityMyAdminData
	 */
	private $adminData;

	/**
	 * @var UbiquityMyAdminViewer
	 */
	private $adminViewer;

	/**
	 * @var UbiquityMyAdminFiles
	 */
	private $adminFiles;

	public function index(){
		$semantic=$this->jquery->semantic();
		$dbs=$this->getTableNames();
		$menu=$semantic->htmlMenu("menuDbs");
		$menu->setVertical()->setInverted();
		foreach ($dbs as $table){
			$model=$this->getModelsNS()."\\".ucfirst($table);
			$file=\str_replace("\\", DS, ROOT . DS.$model).".php";
			$find=Autoloader::tryToRequire($file);
			if ($find){
				$count=DAO::count($model);
				$item=$menu->addItem(ucfirst($table));
				$item->addLabel($count);
				$item->setProperty("data-ajax", $table);
			}
		}
		$menu->getOnClick($this->getAdminFiles()->getAdminBaseRoute()."/showTable","#divTable",["attr"=>"data-ajax"]);
		$menu->onClick("$('.ui.label.left.pointing.teal').removeClass('left pointing teal');$(this).find('.ui.label').addClass('left pointing teal');");
		$this->jquery->compile($this->view);
		$this->loadView($this->getAdminFiles()->getViewIndex());
	}

	public function showTable($table){
		$this->_showTable($table);
		$model=$this->getModelsNS()."\\".ucfirst($table);
		$this->jquery->compile($this->view);
		$this->loadView($this->getAdminFiles()->getViewShowTable(),["classname"=>$model]);
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
			$this->jquery->exec("$('.active').removeClass('active');$('.ui.label.left.pointing.teal').removeClass('left pointing teal active');$(\"[data-ajax='".$table."']\").addClass('active');$(\"[data-ajax='".$table."']\").find('.ui.label').addClass('left pointing teal');",true);
			$this->showTable($table);
			$this->jquery->exec("$(\"tr[data-ajax='".$id."']\").click();",true);
			echo $this->jquery->compile();
		}
	}

	protected function _showTable($table){
		$adminRoute=$this->getAdminFiles()->getAdminBaseRoute();
		$_SESSION["table"]= $table;
		$semantic=$this->jquery->semantic();
		$model=$this->getModelsNS()."\\".ucfirst($table);

		$datas=DAO::getAll($model);
		$modal=($this->getAdminViewer()->isModal($datas, $model)?"modal":"no");
		$lv=$semantic->dataTable("lv", $model, $datas);
		$attributes=$this->getFieldNames($model);

		$lv->setCaptions($this->getAdminViewer()->getCaptions($attributes, $model));
		$lv->setFields($attributes);
		$lv->onPreCompile(function() use ($attributes,&$lv){
			$lv->getHtmlComponent()->colRight(\count($attributes));
		});

		$lv->setIdentifierFunction($this->getIdentifierFunction($model));
		$lv->getOnRow("click", $adminRoute."/showDetail","#table-details",["attr"=>"data-ajax"]);
		$lv->setUrls(["delete"=>$adminRoute."/delete","edit"=>$adminRoute."/edit/".$modal]);
		$lv->setTargetSelector(["delete"=>"#table-messages","edit"=>"#table-details"]);
		$lv->addClass("small very compact");
		$lv->addEditDeleteButtons(false,["ajaxTransition"=>"random"]);
		$lv->setActiveRowSelector("error");
		$this->jquery->getOnClick("#btAddNew", $adminRoute."/new/".$modal,"#table-details");
		return $lv;
	}

	protected function _edit($instance,$modal="no"){
		$_SESSION["instance"]=$instance;
		$modal=($modal=="modal");
		$form=$this->getAdminViewer()->getForm("frmEdit",$instance);
		$this->jquery->click("#action-modal-frmEdit","$('#frmEdit').form('submit');",false);
		if(!$modal){
			$this->jquery->click("#bt-cancel","$('#form-container').transition('drop');");
			$this->jquery->compile($this->view);
			$this->loadView($this->getAdminFiles()->getViewEditTable(),["modal"=>$modal]);
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

	public function new($modal="no"){
		$model=$this->getModelsNS()."\\".ucfirst($_SESSION["table"]);
		$instance=new $model();
		$instance->_new=true;
		$this->_edit($instance,$modal);
	}

	public function update(){
		$message=$this->jquery->semantic()->htmlMessage("msgUpdate","The changes have been correctly saved","info");
		$instance=@$_SESSION["instance"];
		$className=\get_class($instance);
		$relations = OrmUtils::getManyToOneFields($className);
		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ($fieldTypes as $property=>$type){
			if($type=="boolean"){
				if(isset($_POST[$property])){
					$_POST[$property]=1;
				}else{
					$_POST[$property]=0;
				}
			}
		}
		RequestUtils::setValuesToObject($instance,$_POST);
		foreach ($relations as $member){
			if($this->getAdminData()->getUpdateManyToOneInForm()){
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
			}
			if($update){
				if($this->getAdminData()->getUpdateManyToManyInForm()){
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
				$this->jquery->get($this->getAdminFiles()->getAdminBaseRoute()."/refreshTable","#lv","{}","",false,"replaceWith");
			}else{
				$message->setContent("An error has occurred. Can not save changes.")->setStyle("error")->setIcon("warning circle");
			}
			echo $message;
			echo $this->jquery->compile($this->view);
		}
	}

	private function getIdentifierFunction($model){
		$pks=$this->getPks($model);
		return function($index,$instance) use ($pks){
			$values=[];
			foreach ($pks as $pk){
				$getter="get".ucfirst($pk);
				if(method_exists($instance, $getter)){
					$values[]=$instance->{$getter}();
				}
			}
			return implode("_", $values);
		};
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
			$message=$this->showConfMessage("Confirmez la suppression de `".$instanceString."`?", "", $this->getAdminFiles()->getAdminBaseRoute()."/delete/{$ids}", "#table-messages", $ids);
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

	private function showSimpleMessage($content,$type,$icon="info",$timeout=NULL){
		$semantic=$this->jquery->semantic();
		$message=$semantic->htmlMessage("msg-".rand(0,50),$content,$type);
		$message->setIcon($icon." circle");
		$message->setDismissable();
		if(isset($timeout))
			$message->setTimeout(3000);
		return $message;
	}

	private function showConfMessage($content,$type,$url,$responseElement,$data,$attributes=NULL){
		$messageDlg=$this->showSimpleMessage($content, $type,"help circle");
		$btOkay=new HtmlButton("bt-okay","Confirmer","positive");
		$btOkay->addIcon("check circle");
		$btOkay->postOnClick($url,"{data:'".$data."'}",$responseElement,$attributes);
		$btCancel=new HtmlButton("bt-cancel","Annuler","negative");
		$btCancel->addIcon("remove circle outline");
		$btCancel->onClick($messageDlg->jsHide());

		$messageDlg->addContent([$btOkay,$btCancel]);
		return $messageDlg;
	}

	public function showDetail($ids){
		$viewer=$this->getAdminViewer();
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
							$this->getAdminViewer()->displayFkElementList($item, $memberFK, $fkClass, $item);
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
			if($hasElements)
				echo $grid;
			$this->jquery->getOnClick(".showTable", $this->getAdminFiles()->getAdminBaseRoute()."/showTableClick","#divTable",["attr"=>"data-ajax","ajaxTransition"=>"random"]);
			echo $this->jquery->compile($this->view);
		}
	}

	protected function getModelsNS(){
		return Startup::getConfig()["mvcNS"]["models"];
	}

	protected function getUbiquityMyAdminData(){
		return new UbiquityMyAdminData();
	}

	protected function getUbiquityMyAdminViewer(){
		return new UbiquityMyAdminViewer($this);
	}

	protected function getUbiquityMyAdminFiles(){
		return new UbiquityMyAdminFiles();
	}

	private function getSingleton($value,$method){
		if(!isset($value)){
			$value=$this->$method();
		}
		return $value;
	}

	/**
	 * @return UbiquityMyAdminData
	 */
	public function getAdminData(){
		return $this->getSingleton($this->adminData, "getUbiquityMyAdminData");
	}

	/**
	 * @return UbiquityMyAdminViewer
	 */
	public function getAdminViewer(){
		return $this->getSingleton($this->adminViewer, "getUbiquityMyAdminViewer");
	}

	/**
	 * @return UbiquityMyAdminFiles
	 */
	public function getAdminFiles(){
		return $this->getSingleton($this->adminFiles, "getUbiquityMyAdminFiles");
	}

	protected function getTableNames(){
		return $this->getAdminData()->getTableNames();
	}

	protected function getFieldNames($model){
		return $this->getAdminData()->getFieldNames($model);
	}
}

