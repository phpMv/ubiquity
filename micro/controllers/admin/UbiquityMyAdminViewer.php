<?php

namespace micro\controllers\admin;

use Ajax\JsUtils;
use Ajax\semantic\widgets\dataform\DataForm;
use micro\orm\OrmUtils;
use Ajax\service\JArray;
use micro\orm\DAO;
use micro\orm\parser\Reflexion;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\common\html\HtmlCollection;
use Ajax\common\html\BaseHtml;

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
	 * Returns the form for adding or modifying an object
	 * @param string $identifier
	 * @param object $instance the object to add or modify
	 * @return DataForm
	 */
	public function getForm($identifier,$instance){
		$form=$this->jquery->semantic()->dataForm($identifier, $instance);
		$className=\get_class($instance);
		$fields=$this->controller->getAdminData()->getFormFieldNames($className);
		$form->setFields($fields);

		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ($fieldTypes as $property=>$type){
			switch ($type){
				case "boolean": case "bool":
					$form->fieldAsCheckbox($property);
					break;
				case "int": case "integer":
					$form->fieldAsInput($property,["inputType"=>"number"]);
					break;
			}
		}
		$this->relationMembersInForm($form, $instance, $className);
		$form->setCaptions($this->getFormCaptions($form->getInstanceViewer()->getVisibleProperties(),$className,$instance));
		$form->setSubmitParams($this->controller->getAdminFiles()->getAdminBaseRoute()."/update", "#table-details");
		return $form;
	}

	/**
	 * Condition to determine if the edit or add form is modal for $model objects
	 * @param array $objects
	 * @param string $model
	 * @return boolean
	 */
	public function isModal($objects,$model){
		return \count($objects)>20;
	}

	/**
	 * Returns the captions for list fields in showTable action
	 * @param array $captions
	 * @param string $className
	 */
	public function getCaptions($captions,$className){
		return array_map("ucfirst", $captions);
	}

	/**
	 * Returns the captions for form fields
	 * @param array $captions
	 * @param string $className
	 */
	public function getFormCaptions($captions,$className,$instance){
		return array_map("ucfirst", $captions);
	}

	public function getFkHeaderElement($member,$className,$object){
		return new HtmlHeader("",4,$member,"content");
	}

	public function getFkHeaderList($member,$className,$list){
		return new HtmlHeader("",4,$member." (".\count($list).")","content");
	}

	/**
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return BaseHtml
	 */
	public function getFkElement($member,$className,$object){
		return $this->jquery->semantic()->htmlLabel("element-".$className.".".$member,$object."");
	}

	/**
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return HtmlCollection
	 */
	public function getFkList($member,$className,$list){
		$element=$this->jquery->semantic()->htmlList("list-".$className.".".$member);
		return $element->addClass("animated divided celled");
	}

	public function displayFkElementList($element,$member,$className,$object){

	}

	protected function relationMembersInForm($form,$instance,$className){
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
