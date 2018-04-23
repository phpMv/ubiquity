<?php

namespace Ubiquity\controllers\admin\viewers;

use Ajax\common\html\HtmlCollection;
use Ajax\common\html\BaseHtml;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\semantic\html\elements\HtmlIconGroups;
use Ajax\semantic\widgets\dataform\DataForm;
use Ubiquity\orm\OrmUtils;
use Ajax\semantic\widgets\datatable\DataTable;
use Ubiquity\controllers\admin\interfaces\HasModelViewerInterface;
use Ajax\JsUtils;
use Ajax\service\JArray;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\orm\DAO;
use Ubiquity\controllers\crud\CRUDHelper;
use Ajax\common\html\HtmlDoubleElement;

class ModelViewer {
	
	/**
	 * @var JsUtils
	 */
	private $jquery;
	
	/**
	 * @var HasModelViewerInterface
	 */
	protected $controller;
	
	public function __construct(HasModelViewerInterface $controller){
		$this->jquery = $controller->jquery;
		$this->controller=$controller;
	}
	/**
	 * Returns the form for adding or modifying an object
	 *
	 * @param string $identifier
	 * @param object $instance
	 *        	the object to add or modify
	 * @return DataForm
	 */
	public function getForm($identifier, $instance) {
		$type = ($instance->_new) ? "new" : "edit";
		$messageInfos = [ "new" => [ "icon" => HtmlIconGroups::corner ( "table", "plus", "big" ),"message" => "New object creation" ],"edit" => [ "icon" => HtmlIconGroups::corner ( "table", "edit", "big" ),"message" => "Editing an existing object" ] ];
		$message = $messageInfos [$type];
		$form = $this->jquery->semantic ()->dataForm ( $identifier, $instance );
		$className = \get_class ( $instance );
		$fields = $this->controller->_getAdminData ()->getFormFieldNames ( $className );
		$form->setFields ( $fields );
		$form->insertField ( 0, "_message" );
		$form->fieldAsMessage ( "_message", [ "icon" => $message ["icon"] ] );
		$instance->_message = $className;
		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		foreach ( $fieldTypes as $property => $type ) {
			switch ($type) {
				case "tinyint(1)" :
					$form->fieldAsCheckbox ( $property );
					break;
				case "int" :
				case "integer" :
					$form->fieldAsInput ( $property, [ "inputType" => "number" ] );
					break;
				case "date" :
					$form->fieldAsInput ( $property, [ "inputType" => "date" ] );
					break;
				case "datetime" :
					$form->fieldAsInput ( $property, [ "inputType" => "datetime-local" ] );
					break;
			}
		}
		$this->relationMembersInForm ( $form, $instance, $className );
		$form->setCaptions ( $this->getFormCaptions ( $form->getInstanceViewer ()->getVisibleProperties (), $className, $instance ) );
		$form->setCaption ( "_message", $message ["message"] );
		$form->setSubmitParams ( $this->controller->_getBaseRoute () . "/update", "#frm-add-update" );
		return $form;
	}
	
	/**
	 * Returns the dataTable responsible for displaying instances of the model
	 *
	 * @param array $instances
	 *        	objects to display
	 * @param string $model
	 *        	model class name (long name)
	 * @return DataTable
	 */
	public function getModelDataTable($instances, $model) {
		$adminRoute = $this->controller->_getBaseRoute ();
		$semantic = $this->jquery->semantic ();
		
		$modal = ($this->isModal ( $instances, $model ) ? "modal" : "no");
		$lv = $semantic->dataTable ( "lv", $model, $instances );
		$attributes = $this->controller->_getAdminData()->getFieldNames ( $model );
		
		$lv->setCaptions ( $this->getCaptions ( $attributes, $model ) );
		$lv->setFields ( $attributes );
		$lv->onPreCompile ( function () use ($attributes, &$lv) {
			$lv->getHtmlComponent ()->colRight ( \count ( $attributes ) );
		} );
			
		$lv->setIdentifierFunction ( CRUDHelper::getIdentifierFunction ( $model ) );
		if($this->showDetailsOnDataTableClick()){
			$lv->getOnRow ( "click", $adminRoute . "/showDetail", "#table-details", [ "attr" => "data-ajax" ] );
			$lv->setActiveRowSelector ( "error" );
		}
		$lv->setUrls ( [ "delete" => $adminRoute . "/delete","edit" => $adminRoute . "/edit/" . $modal ] );
		$lv->setTargetSelector ( [ "delete" => "#table-messages","edit" => "#frm-add-update" ] );
		$lv->addClass ( "small very compact" );
		$lv->addEditDeleteButtons ( false, [ "ajaxTransition" => "random" ], function ($bt) {
			$bt->addClass ( "circular" );
		}, function ($bt) {
			$bt->addClass ( "circular" );
		} );
		return $lv;
	}
	
	/**
	 * Condition to determine if the edit or add form is modal for $model objects
	 *
	 * @param array $objects
	 * @param string $model
	 * @return boolean
	 */
	public function isModal($objects, $model) {
		return \count ( $objects ) > 5;
	}
	
	/**
	 * Returns the captions for list fields in showTable action
	 *
	 * @param array $captions
	 * @param string $className
	 */
	public function getCaptions($captions, $className) {
		return array_map ( "ucfirst", $captions );
	}
	
	/**
	 * Returns the captions for form fields
	 *
	 * @param array $captions
	 * @param string $className
	 */
	public function getFormCaptions($captions, $className, $instance) {
		return array_map ( "ucfirst", $captions );
	}
	
	/**
	 * Returns the header for a single foreign object (element is an instance, issue from ManyToOne)
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return HtmlHeader
	 */
	public function getFkHeaderElement($member, $className, $object) {
		return new HtmlHeader( "", 4, $member, "content" );
	}
	
	/**
	 * Returns the header for a list of foreign objects (issue from oneToMany or ManyToMany) 
	 * @param string $member
	 * @param string $className
	 * @param array $list
	 * @return HtmlHeader
	 */
	public function getFkHeaderList($member, $className, $list) {
		return new HtmlHeader( "", 4, $member . " (" . \count ( $list ) . ")", "content" );
	}
	
	/**
	 * Returns a component for displaying a single foreign object (manyToOne relation)
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return BaseHtml
	 */
	public function getFkElement($member, $className, $object) {
		return $this->jquery->semantic ()->htmlLabel ( "element-" . $className . "." . $member, $object . "" );
	}
	
	/**
	 * Returns a list component for displaying a collection of foreign objects (*ToMany relations)
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return HtmlCollection
	 */
	public function getFkList($member, $className, $list) {
		$element = $this->jquery->semantic ()->htmlList ( "list-" . $className . "." . $member );
		return $element->addClass ( "animated divided celled" );
	}
	
	/**
	 * Returns a component for displaying a foreign object
	 * @param string $memberFK
	 * @param mixed $objectFK
	 * @param string $fkClass
	 * @param string $fkTable
	 * @return string|NULL
	 */
	public function getFkMemberElement($memberFK,$objectFK,$fkClass,$fkTable){
		$header=new HtmlHeader("", 4, $memberFK, "content");
		if (is_array($objectFK) || $objectFK instanceof \Traversable) {
			$header=$this->getFkHeaderList($memberFK, $fkClass, $objectFK);
			$element=$this->getFkList($memberFK, $fkClass, $objectFK);
			foreach ( $objectFK as $oItem ) {
				if (method_exists($oItem, "__toString")) {
					$id=(CRUDHelper::getIdentifierFunction($fkClass))(0, $oItem);
					$item=$element->addItem($oItem . "");
					$item->setProperty("data-ajax", $fkTable . "." . $id);
					$item->addClass("showTable");
					$this->displayFkElementList($item, $memberFK, $fkClass, $oItem);
				}
			}
		} else {
			if (method_exists($objectFK, "__toString")) {
				$header=$this->getFkHeaderElement($memberFK, $fkClass, $objectFK);
				$id=(CRUDHelper::getIdentifierFunction($fkClass))(0, $objectFK);
				$element=$this->getFkElement($memberFK, $fkClass, $objectFK);
				$element->setProperty("data-ajax", $fkTable . "." . $id)->addClass("showTable");
			}
		}
		if(isset($element)){
			return $header.$element;
		}
		return null;
	}
	
	/**
	 * To modify for displaying an element in a list component of foreign objects
	 * @param HtmlDoubleElement $element
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 */
	public function displayFkElementList($element, $member, $className, $object) {
	}
	
	public function showDetailsOnDataTableClick(){
		return true;
	}
	
	protected function relationMembersInForm($form, $instance, $className) {
		$relations = OrmUtils::getFieldsInRelations ( $className );
		foreach ( $relations as $member ) {
			if ($this->controller->_getAdminData ()->getUpdateManyToOneInForm () && OrmUtils::getAnnotationInfoMember ( $className, "#manyToOne", $member ) !== false) {
				$this->manyToOneFormField ( $form, $member, $className, $instance );
			} elseif ($this->controller->_getAdminData ()->getUpdateOneToManyInForm () && ($annot = OrmUtils::getAnnotationInfoMember ( $className, "#oneToMany", $member )) !== false) {
				$this->oneToManyFormField ( $form, $member, $instance, $annot );
			} elseif ($this->controller->_getAdminData ()->getUpdateManyToManyInForm () && ($annot = OrmUtils::getAnnotationInfoMember ( $className, "#manyToMany", $member )) !== false) {
				$this->manyToManyFormField ( $form, $member, $instance, $annot );
			}
		}
	}
	
	protected function manyToOneFormField(DataForm $form, $member, $className, $instance) {
		$joinColumn = OrmUtils::getAnnotationInfoMember ( $className, "#joinColumn", $member );
		if ($joinColumn) {
			$fkObject = Reflexion::getMemberValue ( $instance, $member );
			$fkClass = $joinColumn ["className"];
			if ($fkObject === null) {
				$fkObject = new $fkClass ();
			}
			$fkId = OrmUtils::getFirstKey ( $fkClass );
			$fkIdGetter = "get" . \ucfirst ( $fkId );
			if (\method_exists ( $fkObject, "__toString" ) && \method_exists ( $fkObject, $fkIdGetter )) {
				$fkField = $joinColumn ["name"];
				$fkValue = OrmUtils::getFirstKeyValue ( $fkObject );
				if (! Reflexion::setMemberValue ( $instance, $fkField, $fkValue )) {
					$instance->{$fkField} = OrmUtils::getFirstKeyValue ( $fkObject );
					$form->addField ( $fkField );
				}
				$form->fieldAsDropDown ( $fkField, JArray::modelArray ( DAO::getAll ( $fkClass ), $fkIdGetter, "__toString" ) );
				$form->setCaption ( $fkField, \ucfirst ( $member ) );
			}
		}
	}
	
	protected function oneToManyFormField(DataForm $form, $member, $instance, $annot) {
		$newField = $member . "Ids";
		$fkClass = $annot ["className"];
		$fkId = OrmUtils::getFirstKey ( $fkClass );
		$fkIdGetter = "get" . \ucfirst ( $fkId );
		$fkInstances = DAO::getOneToMany ( $instance, $member );
		$form->addField ( $newField );
		$ids = \array_map ( function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter} ();
		}, $fkInstances );
		$instance->{$newField} = \implode ( ",", $ids );
		$form->fieldAsDropDown ( $newField, JArray::modelArray ( DAO::getAll ( $fkClass ), $fkIdGetter, "__toString" ), true );
		$form->setCaption ( $newField, \ucfirst ( $member ) );
	}
	
	protected function manyToManyFormField(DataForm $form, $member, $instance, $annot) {
		$newField = $member . "Ids";
		$fkClass = $annot ["targetEntity"];
		$fkId = OrmUtils::getFirstKey ( $fkClass );
		$fkIdGetter = "get" . \ucfirst ( $fkId );
		$fkInstances = DAO::getManyToMany ( $instance, $member );
		$form->addField ( $newField );
		$ids = \array_map ( function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter} ();
		}, $fkInstances );
			$instance->{$newField} = \implode ( ",", $ids );
			$form->fieldAsDropDown ( $newField, JArray::modelArray ( $this->controller->_getAdminData ()->getManyToManyDatas ( $fkClass, $instance, $member ), $fkIdGetter, "__toString" ), true, [ "jsCallback" => function ($elm) {
				$elm->getField ()->asSearch ();
			} ] );
		$form->setCaption ( $newField, \ucfirst ( $member ) );
	}
	
}

