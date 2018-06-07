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
use Ajax\semantic\html\elements\HtmlButton;
use Ajax\semantic\widgets\datatable\PositionInTable;
use Ajax\semantic\html\elements\HtmlLabel;

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
		$this->setFormFieldsComponent($form, $fieldTypes);
		$this->relationMembersInForm ( $form, $instance, $className );
		$form->setCaptions ( $this->getFormCaptions ( $form->getInstanceViewer ()->getVisibleProperties (), $className, $instance ) );
		$form->setCaption ( "_message", $message ["message"] );
		$form->setSubmitParams ( $this->controller->_getBaseRoute () . "/update", "#frm-add-update" );
		return $form;
	}
	
	/**
	 * Sets the components for each field
	 * @param DataForm $form
	 * @param array $fieldTypes associative array of field names (keys) and types (values)
	 */
	public function setFormFieldsComponent(DataForm $form,$fieldTypes){
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
	}
	
	public function getModelDataElement($instance,$model,$modal){
		$adminRoute = $this->controller->_getBaseRoute ();
		$semantic = $this->jquery->semantic ();
		$fields = $this->controller->_getAdminData ()->getElementFieldNames( $model );
		
		$dataElement = $semantic->dataElement( "de", $instance );
		$pk=OrmUtils::getFirstKeyValue($instance);
		$dataElement->getInstanceViewer()->setIdentifierFunction(function() use($pk){return $pk;});
		$dataElement->setFields($fields);
		$dataElement->setCaptions($this->getElementCaptions($fields, $model, $instance));

		$fkInstances=CRUDHelper::getFKIntances($instance, $model);
		foreach ( $fkInstances as $member=>$fkInstanceArray ) {
			if(array_search($member, $fields)!==false){
				$dataElement->setValueFunction($member, function() use($fkInstanceArray,$member){
					return $this->getFkMemberElement($member,$fkInstanceArray["objectFK"],$fkInstanceArray["fkClass"],$fkInstanceArray["fkTable"]);
				});
			}	
		}
		return $dataElement;
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
	public function getModelDataTable($instances, $model,$page=1) {
		$adminRoute = $this->controller->_getBaseRoute ();
		$semantic = $this->jquery->semantic ();
		
		$modal = ($this->isModal ( $instances, $model ) ? "modal" : "no");
		$dataTable = $this->getDataTableInstance( $instances,$model,$page );
		$attributes = $this->controller->_getAdminData()->getFieldNames ( $model );
		
		$dataTable->setCaptions ( $this->getCaptions ( $attributes, $model ) );
		$dataTable->setButtons($this->getDataTableRowButtons());
		$dataTable->setFields ( $attributes );

		$dataTable->setIdentifierFunction ( CRUDHelper::getIdentifierFunction ( $model ) );
		if($this->showDetailsOnDataTableClick()){
			$dataTable->getOnRow ( "click", $adminRoute . "/showDetail", "#table-details", [ "attr" => "data-ajax" ] );
			$dataTable->setActiveRowSelector ( "error" );
		}
		$dataTable->setUrls ( [ "refresh"=>$adminRoute . "/refresh_","delete" => $adminRoute . "/delete","edit" => $adminRoute . "/edit/" . $modal ,"display"=> $adminRoute."/display/".$modal] );
		$dataTable->setTargetSelector ( [ "delete" => "#table-messages","edit" => "#frm-add-update" ,"display"=>"#table-details" ] );
		$dataTable->addClass ( "small very compact" );
		$lbl=new HtmlLabel("search-query","<span id='search-query-content'></span>");
		$icon=$lbl->addIcon("delete",false);
		$lbl->wrap("<span>","</span>");
		$lbl->setProperty("style", "display: none;");
		$icon->getOnClick($adminRoute."/refreshTable","#lv",["jqueryDone"=>"replaceWith","hasLoader"=>"internal"]);
		
		$dataTable->addItemInToolbar($lbl);
		$dataTable->addSearchInToolbar();
		$dataTable->setToolbarPosition(PositionInTable::FOOTER);
		$dataTable->getToolbar()->setSecondary();
		$this->addAllButtons($dataTable, $attributes);
		return $dataTable;
	}
	
	public function recordsPerPage($model,$totalCount=0){
		if($totalCount>6)
			return 6;
		return ;
	}
	
	public function getGroupByFields(){
		return;
	}
	
	protected function getDataTableInstance($instances,$model,$page=1):DataTable{
		$totalCount=DAO::count($model,$this->controller->_getInstancesFilter($model));
		$semantic = $this->jquery->semantic ();
		$recordsPerPage=$this->recordsPerPage($model,$totalCount);
		if(is_numeric($recordsPerPage)){
			$grpByFields=$this->getGroupByFields();
			if(is_array($grpByFields)){
				$dataTable = $semantic->dataTable( "lv", $model, $instances );
				$dataTable->setGroupByFields($grpByFields);
			}else{
				$dataTable = $semantic->jsonDataTable( "lv", $model, $instances );
			}
			$dataTable->paginate($page,$totalCount,$recordsPerPage,5);
			$dataTable->onActiveRowChange('$("#table-details").html("");');
			$dataTable->onSearchTerminate('$("#search-query-content").html(data);$("#search-query").show();$("#table-details").html("");');
		}else{
			$dataTable = $semantic->dataTable( "lv", $model, $instances );
		}
		return $dataTable;
	}
	
	/**
	 * Returns an array of buttons ["display","edit","delete"] to display for each row in dataTable
	 * @return string[]
	 */
	protected function getDataTableRowButtons(){
		return ["edit","delete"];
	}
	
	public function addAllButtons(DataTable $dataTable,$attributes){
		$dataTable->onPreCompile ( function () use ($attributes, &$dataTable) {
			$dataTable->getHtmlComponent ()->colRightFromRight ( 0 );
		} );
		$dataTable->addAllButtons( false, [ "ajaxTransition" => "random" ], function ($bt) {
			$bt->addClass ( "circular" );
		}, function ($bt) {
			$bt->addClass ( "circular" );
		}, function ($bt) {
			$bt->addClass ( "circular" );
		});
		$dataTable->setDisplayBehavior(["jsCallback"=>'$("#dataTable").hide();',"ajaxTransition"=>"random"]);
	}
	
	public function confirmButtons(HtmlButton $confirmBtn,HtmlButton $cancelBtn){
		
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
	 * Returns the captions for DataElement fields
	 *
	 * @param array $captions
	 * @param string $className
	 */
	public function getElementCaptions($captions, $className, $instance) {
		return array_map ( "ucfirst", $captions );
	}
	
	/**
	 * Returns the header for a single foreign object (element is an instance, issue from ManyToOne), (from DataTable)
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return HtmlHeader
	 */
	public function getFkHeaderElementDetails($member, $className, $object) {
		return new HtmlHeader( "", 4, $member, "content" );
	}
	
	/**
	 * Returns the header for a list of foreign objects (issue from oneToMany or ManyToMany), (from DataTable)
	 * @param string $member
	 * @param string $className
	 * @param array $list
	 * @return HtmlHeader
	 */
	public function getFkHeaderListDetails($member, $className, $list) {
		return new HtmlHeader( "", 4, $member . " (" . \count ( $list ) . ")", "content" );
	}
	
	/**
	 * Returns a component for displaying a single foreign object (manyToOne relation), (from DataTable)
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return BaseHtml
	 */
	public function getFkElementDetails($member, $className, $object) {
		return $this->jquery->semantic ()->htmlLabel ( "element-" . $className . "." . $member, $object . "" );
	}
	
	/**
	 * Returns a list component for displaying a collection of foreign objects (*ToMany relations), (from DataTable)
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return HtmlCollection
	 */
	public function getFkListDetails($member, $className, $list) {
		$element = $this->jquery->semantic ()->htmlList ( "list-" . $className . "." . $member );
		$element->setMaxVisible(15);
		
		return $element->addClass ( "animated divided celled" );
	}
	
	/**
	 * Returns a component for displaying a foreign object (from DataTable)
	 * @param string $memberFK
	 * @param mixed $objectFK
	 * @param string $fkClass
	 * @param string $fkTable
	 * @return string|NULL
	 */
	public function getFkMemberElementDetails($memberFK,$objectFK,$fkClass,$fkTable){
		$_fkClass=str_replace("\\", ".",$fkClass);
		$header=new HtmlHeader("", 4, $memberFK, "content");
		if (is_array($objectFK) || $objectFK instanceof \Traversable) {
			$header=$this->getFkHeaderListDetails($memberFK, $fkClass, $objectFK);
			$element=$this->getFkListDetails($memberFK, $fkClass, $objectFK);
			foreach ( $objectFK as $oItem ) {
				if (method_exists($oItem, "__toString")) {
					$id=(CRUDHelper::getIdentifierFunction($fkClass))(0, $oItem);
					$item=$element->addItem($oItem . "");
					$item->setProperty("data-ajax", $_fkClass . ":" . $id);
					$item->addClass("showTable");
					$this->displayFkElementListDetails($item, $memberFK, $fkClass, $oItem);
				}
			}
		} else {
			if (method_exists($objectFK, "__toString")) {
				$header=$this->getFkHeaderElementDetails($memberFK, $fkClass, $objectFK);
				$id=(CRUDHelper::getIdentifierFunction($fkClass))(0, $objectFK);
				$element=$this->getFkElementDetails($memberFK, $fkClass, $objectFK);
				$element->setProperty("data-ajax", $_fkClass . ":" . $id)->addClass("showTable");
			}
		}
		if(isset($element)){
			return [$header,$element];
		}
		return null;
	}
	
	/**
	 * To modify for displaying an element in a list component of foreign objects (from DataTable)
	 * @param HtmlDoubleElement $element
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 */
	public function displayFkElementListDetails($element, $member, $className, $object) {
	}
	
	/**
	 * Returns a component for displaying a foreign object (from DataElement)
	 * @param string $memberFK
	 * @param mixed $objectFK
	 * @param string $fkClass
	 * @param string $fkTable
	 * @return string|NULL
	 */
	public function getFkMemberElement($memberFK,$objectFK,$fkClass,$fkTable){
		$element="";
		$_fkClass=str_replace("\\", ".",$fkClass);
		if (is_array($objectFK) || $objectFK instanceof \Traversable) {
			$element=$this->getFkList($memberFK,$objectFK);
			foreach ( $objectFK as $oItem ) {
				if (method_exists($oItem, "__toString")) {
					$id=(CRUDHelper::getIdentifierFunction($fkClass))(0, $oItem);
					$item=$element->addItem($oItem . "");
					$item->setProperty("data-ajax", $_fkClass . ":" . $id);
					$item->addClass("showTable");
					$this->displayFkElementList($item, $memberFK, $fkClass, $oItem);
				}
			}
		} else {
			if (method_exists($objectFK, "__toString")) {
				$id=(CRUDHelper::getIdentifierFunction($fkClass))(0, $objectFK);
				$element=$this->getFkElement($memberFK, $fkClass, $objectFK);
				$element->setProperty("data-ajax", $_fkClass . ":" . $id)->addClass("showTable");
			}
		}
		return $element;
	}
	
	/**
	 * Returns a list component for displaying a collection of foreign objects (*ToMany relations), (from DataElement) 
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return HtmlCollection
	 */
	public function getFkList($member, $list) {
		$element = $this->jquery->semantic ()->htmlList ( "list-". $member );
		$element->setMaxVisible(10);
		return $element->addClass ( "animated" );
	}
	
	/**
	 * To modify for displaying an element in a list component of foreign objects, (from DataElement)
	 * @param HtmlDoubleElement $element
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 */
	public function displayFkElementList($element, $member, $className, $object) {
	}
	
	/**
	 * Returns a component for displaying a single foreign object (manyToOne relation), (from DataElement)
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return BaseHtml
	 */
	public function getFkElement($member, $className, $object) {
		return $this->jquery->semantic ()->htmlLabel ( "element-" . $className . "." . $member, $object . "" );
	}
	
	/**
	 * To override to make sure that the detail of a clicked object is displayed or not
	 * @return boolean Return true if you want to see details
	 */
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

