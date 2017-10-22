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
	 * @param object $instance
	 * @return DataForm
	 */
	public function getForm($instance){
		$form=$this->jquery->semantic()->dataForm("frmEdit", $instance);
		$className=\get_class($instance);
		$form->setFields($this->controller->getAdminData()->getFormFieldNames($className));
		$form->setSubmitParams("Admin/update", "#table-details");
		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ($fieldTypes as $property=>$type){
			switch ($type){
				case "boolean":
					$form->fieldAsCheckbox($property);
					break;
				case "int":
					$form->fieldAsInput($property,["inputType"=>"number"]);
					break;
			}
		}
		$relations = OrmUtils::getFieldsInRelations($className);
		foreach ($relations as $member){
			if(OrmUtils::getAnnotationInfoMember($className, "#manyToOne",$member)!==false){
				$joinColumn=OrmUtils::getAnnotationInfoMember($className, "#joinColumn", $member);
				if($joinColumn){
					$fkObject=Reflexion::getMemberValue($instance, $member);
					$fkClass=$joinColumn["className"];
					$fkId=OrmUtils::getFirstKey($fkClass);
					$fkIdGetter="get".\ucfirst($fkId);
					if(\method_exists($fkObject, "__toString") && \method_exists($fkObject, $fkIdGetter)){
						$fkField=$joinColumn["name"];

						$fkValue=OrmUtils::getFirstKeyValue($fkObject);
						if(!Reflexion::setMemberValue($instance, $fkField, $fkValue)){
							$instance->$fkField=OrmUtils::getFirstKeyValue($fkObject);
							$form->addField($fkField);
						}
						$form->fieldAsDropDown($fkField,JArray::modelArray(DAO::getAll($fkClass),$fkIdGetter,"__toString"));
						$form->setCaption($fkField, \ucfirst($member));
					}
				}
			}
		}

		return $form;
	}
}