<?php

namespace micro\controllers\admin;

use Ajax\JsUtils;
use Ajax\semantic\widgets\dataform\DataForm;
use micro\orm\OrmUtils;
use Ajax\service\JArray;
use micro\orm\DAO;
use micro\orm\parser\Reflexion;

/**
 * @author jc
 *
 */
class UbiquityMyAdminViewer {
	/**
	 * @var JsUtils
	 */
	private $jquery;

	/**
	 * @var UbiquityMyAdminBaseController
	 */
	private $controller;
	public function __construct(UbiquityMyAdminBaseController $controller){
		$this->jquery=$controller->jquery;
		$this->controller=$controller;
	}

	/**
	 * @param string $identifier
	 * @param object $instance
	 * @param boolean $modal
	 * @return DataForm
	 */
	public function getForm($identifier,$instance,$modal=false){
		$form=$this->jquery->semantic()->dataForm($identifier, $instance);
		$className=\get_class($instance);
		$form->setFields($this->controller->getAdminData()->getFormFieldNames($className));

		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ($fieldTypes as $property=>$type){
			switch ($type){
				case "boolean":
					$form->fieldAsCheckbox($property);
					break;
				case "int":
					$form->fieldAsInput($property,["inputType"=>"number"]);
					break;
				case "password":
					$form->fieldAs($property, ["inputType"=>"password"]);
					break;
				case "email":
					$form->fieldAsInput($property,["inputType"=>"email","rules"=>["email"]]);
					break;
			}
		}
		$relations = OrmUtils::getFieldsInRelations($className);
		foreach ($relations as $member){
			if($this->controller->getAdminData()->getUpdateManyToOneInForm() && OrmUtils::getAnnotationInfoMember($className, "#manyToOne",$member)!==false){
				$this->manyToOneFormField($form, $member, $className, $instance);
			}elseif($this->controller->getAdminData()->getUpdateOneToManyInForm() && ($annot=OrmUtils::getAnnotationInfoMember($className, "#oneToMany",$member))!==false){
				$this->oneToManyFormField($form, $member, $instance,$annot);
			}elseif($this->controller->getAdminData()->getUpdateManyToManyInForm() && ($annot=OrmUtils::getAnnotationInfoMember($className, "#manyToMany",$member))!==false){
				$this->manyToManyFormField($form, $member, $instance,$annot);
			}
		}
		$form->setSubmitParams("Admin/update", "#table-details");
		return $form;
	}

	public function isModal($objects,$model){
		return \count($objects)>20;
	}

	protected function manyToOneFormField(DataForm $form,$member,$className,$instance){
		$joinColumn=OrmUtils::getAnnotationInfoMember($className, "#joinColumn", $member);
		if($joinColumn){
			$fkObject=Reflexion::getMemberValue($instance, $member);
			$fkClass=$joinColumn["className"];
			if($fkObject===null){
				$fkObject=new $fkClass();
			}
			$fkId=OrmUtils::getFirstKey($fkClass);
			$fkIdGetter="get".\ucfirst($fkId);
			if(\method_exists($fkObject, "__toString") && \method_exists($fkObject, $fkIdGetter)){
				$fkField=$joinColumn["name"];
				$fkValue=OrmUtils::getFirstKeyValue($fkObject);
				if(!Reflexion::setMemberValue($instance, $fkField, $fkValue)){
					$instance->{$fkField}=OrmUtils::getFirstKeyValue($fkObject);
					$form->addField($fkField);
				}
				$form->fieldAsDropDown($fkField,JArray::modelArray(DAO::getAll($fkClass),$fkIdGetter,"__toString"));
				$form->setCaption($fkField, \ucfirst($member));
			}
		}
	}
	protected function oneToManyFormField(DataForm $form,$member,$instance,$annot){
		$newField=$member."Ids";
		$fkClass=$annot["className"];
		$fkId=OrmUtils::getFirstKey($fkClass);
		$fkIdGetter="get".\ucfirst($fkId);
		$fkInstances=DAO::getOneToMany($instance, $member);
		$form->addField($newField);
		$ids=\array_map(function($elm) use($fkIdGetter){return $elm->{$fkIdGetter}();},$fkInstances);
		$instance->{$newField}=\implode(",", $ids);
		$form->fieldAsDropDown($newField,JArray::modelArray(DAO::getAll($fkClass),$fkIdGetter,"__toString"),true);
		$form->setCaption($newField, \ucfirst($member));
	}

	protected function manyToManyFormField(DataForm $form,$member,$instance,$annot){
		$newField=$member."Ids";
		$fkClass=$annot["targetEntity"];
		$fkId=OrmUtils::getFirstKey($fkClass);
		$fkIdGetter="get".\ucfirst($fkId);
		$fkInstances=DAO::getManyToMany($instance, $member);
		$form->addField($newField);
		$ids=\array_map(function($elm) use($fkIdGetter){return $elm->{$fkIdGetter}();},$fkInstances);
		$instance->{$newField}=\implode(",", $ids);
		$form->fieldAsDropDown($newField,JArray::modelArray($this->controller->getAdminData()->getManyToManyDatas($fkClass, $instance, $member),$fkIdGetter,"__toString"),true,["jsCallback"=>function($elm){$elm->getField()->asSearch();}]);
		$form->setCaption($newField, \ucfirst($member));
	}
}
