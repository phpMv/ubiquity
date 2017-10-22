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

class UbiquityMyAdminBaseController extends ControllerBase{
	private $adminData;
	private $adminViewer;

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
		$menu->getOnClick("Admin/showTable","#divTable",["attr"=>"data-ajax"]);
		$menu->onClick("$('.ui.label.left.pointing.teal').removeClass('left pointing teal');$(this).find('.ui.label').addClass('left pointing teal');");
		$this->jquery->compile($this->view);
		$this->loadView("Admin/index.html");
	}

	public function showTable($table){
		$_SESSION["table"]= $table;
		$semantic=$this->jquery->semantic();
		$model=$this->getModelsNS()."\\".ucfirst($table);

		$datas=DAO::getAll($model);
		$lv=$semantic->dataTable("lv", $model, $datas);
		$attributes=$this->getFieldNames($model);

		$lv->setCaptions(array_map("ucfirst", $attributes));
		$lv->setFields($attributes);
		$lv->onPreCompile(function() use ($attributes,&$lv){
			$lv->getHtmlComponent()->colRight(\count($attributes));
		});

		$lv->setIdentifierFunction($this->getIdentifierFunction($model));
		$lv->getOnRow("click", "Admin/showDetail","#table-details",["attr"=>"data-ajax"]);
		$lv->setUrls(["delete"=>"Admin/delete","edit"=>"Admin/edit"]);
		$lv->setTargetSelector(["delete"=>"#table-messages","edit"=>"#table-details"]);
		$lv->addClass("small very compact");
		$lv->addEditDeleteButtons(false,["ajaxTransition"=>"random"]);
		$lv->setActiveRowSelector("error");

		$this->jquery->compile($this->view);
		$this->loadView("admin\showTable.html",["classname"=>$model]);
	}

	public function edit($ids){
		$instance=$this->getModelInstance($ids);
		$this->getAdminViewer()->getForm("frmEdit",$instance);
		$this->jquery->click("#bt-okay","$('#frmEdit').form('submit');");
		$this->jquery->click("#bt-cancel","$('#form-container').transition('drop');");
		$this->jquery->compile($this->view);
		$this->loadView("admin/editTable.html");
	}

	public function update(){
		\var_dump($_POST);
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

	private function getOneWhere($ids,$table){
		$ids=explode("_", $ids);
		if(sizeof($ids)<1)
			return "";
		$pks=$this->getPks(ucfirst($table));
		$strs=[];
		for($i=0;$i<sizeof($ids);$i++){
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
			$message=$this->showConfMessage("Confirmez la suppression de `".$instanceString."`?", "", "Admin/delete/{$ids}", "#table-messages", $ids);
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
				if(OrmUtils::getAnnotationInfoMember($model, "#oneToMany",$member)!==false){
					$objectFK=DAO::getOneToMany($instance, $member);
				}if(OrmUtils::getAnnotationInfoMember($model, "#manyToMany",$member)!==false){
					$objectFK=DAO::getManyToMany($instance, $member);
				}else{
					$objectFK=Reflexion::getMemberValue($instance, $member);
				}
				$memberFK=$member;

				$header=new HtmlHeader("",4,$memberFK,"content");
				if(is_array($objectFK) || $objectFK instanceof Traversable){
					$element=$semantic->htmlList("");
					$element->addClass("animated divided celled");
					$header->addIcon("folder");
					foreach ($objectFK as $item){
						if(method_exists($item, "__toString")){
							$element->addItem($item."");
							$hasElements=true;
						}
					}
				}else{
					if(method_exists($objectFK, "__toString")){
						$element=$semantic->htmlLabel("",$objectFK."");
						$header->addIcon("file outline");
					}
				}
				if(isset($element)){
					$grid->addCol($wide)->setContent($header.$element);
					$hasElements=true;
				}
			}
			if($hasElements)
				echo $grid;
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

	protected function getTableNames(){
		return $this->getAdminData()->getTableNames();
	}

	protected function getFieldNames($model){
		return $this->getAdminData()->getFieldNames($model);
	}
}

