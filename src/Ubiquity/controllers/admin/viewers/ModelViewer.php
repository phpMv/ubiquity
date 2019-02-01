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
use Ubiquity\utils\base\UString;
use Ajax\semantic\html\collections\form\HtmlFormField;
use Ubiquity\controllers\crud\EditMemberParams;

/**
 * Associated with a CRUDController class
 * Responsible of the display
 * 
 * @author jc
 *
 */
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
	 * @return \Ajax\semantic\widgets\dataform\DataForm
	 */
	public function getForm($identifier, $instance) {
		$form = $this->jquery->semantic()->dataForm( $identifier, $instance);
		$form->setLibraryId("frmEdit");
		$className = \get_class ( $instance );
		$fields = $this->controller->_getAdminData ()->getFormFieldNames ( $className ,$instance);
		$relFields=OrmUtils::getFieldsInRelations_($className);
		
		$this->setFormFields_($fields, $relFields);
		array_unshift($fields, "_message");
		$form->setFields ($fields);
		
		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		$this->setFormFieldsComponent($form, $fieldTypes);
		$this->relationMembersInForm ( $form, $instance, $className,$fields,$relFields );
		OrmUtils::setFieldToMemberNames($fields, $relFields);
		$form->setCaptions ( $this->getFormCaptions ( $fields, $className, $instance ) );
		$message=$this->getFormTitle($form, $instance);
		$form->setCaption ( "_message", $message ["subMessage"] );
		$form->fieldAsMessage ( "_message", [ "icon" => $message ["icon"] ] );
		$instance->_message = $message ["message"];
		$form->setSubmitParams ( $this->controller->_getBaseRoute () . "/update", "#frm-add-update" );
		$form->onGenerateField([$this,'onGenerateFormField']);
		return $form;
	}
	
	/**
	 * Returns a form for member editing
	 * @param string $identifier
	 * @param object $instance
	 * @param string $member
	 * @param string $td
	 * @param string $part
	 * @return \Ajax\semantic\widgets\dataform\DataForm
	 */
	public function getMemberForm($identifier,$instance,$member,$td,$part){
		$editMemberParams=$this->getEditMemberParams_($part);

		$form = $this->jquery->semantic()->dataForm( $identifier, $instance);
		$form->on("dblclick","",true,true);
		$form->setProperty("onsubmit", "return false;");
		$form->addClass("_memberForm");
		$className = \get_class ( $instance );
		$fields=["id",$member];
		$relFields=OrmUtils::getFieldsInRelations_($className);
		$hasRelations=$this->setFormFields_($fields, $relFields);
		$form->setFields ($fields);
		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		$form->fieldAsHidden(0);
		$this->setMemberFormFieldsComponent($form, $fieldTypes);
		if($hasRelations){
			$this->relationMembersInForm ( $form, $instance, $className,$fields,$relFields );
		}
		$form->setCaptions(["",""]);
		$form->onGenerateField(function(HtmlFormField $f,$nb) use($identifier,$editMemberParams){
				if($nb==1){
					$f->setSize("mini");
					if($editMemberParams->getHasButtons()){
						$btO=HtmlButton::icon("btO", "check")->addClass("green mini compact")->onClick("\$('#".$identifier."').trigger('validate');",true,true);
						$btC=HtmlButton::icon("btC", "close")->addClass("mini compact")->onClick("\$('#".$identifier."').trigger('endEdit');");
						$f->wrap("<div class='fields' style='margin:0;'>",[$btO,$btC,"</div>"]);
						if(!$editMemberParams->getHasPopup()){
							$f->setWidth(16)->setProperty("style", "padding-left:0;");
						}
					}
					$f->on("keydown","if(event.which == 13) {\$('#".$identifier."').trigger('validate');}if(event.keyCode===27) {\$('#".$identifier."').trigger('endEdit');}");
					$f->onClick("return false;",true,true);
				}else{
					$f->setProperty("style", "display: none;");
				}
		});
		$form->setSubmitParams ( $this->controller->_getBaseRoute () . "/updateMember/".$member."/".$editMemberParams->getUpdateCallback(), "#".$td ,["attr"=>"","hasLoader"=>false,"jsCallback"=>"$(self).remove();","jqueryDone"=>"html"]);
		if($editMemberParams->getHasPopup()){
			$endEdit="\$('#".$identifier."').html();\$('.popup').hide();\$('#".$td."').popup('destroy');";
			$validate=$endEdit;
		}else{
			$endEdit="let td=\$('#".$td."');td.html(td.data('originalText'));";
			$validate="";
		}
		$form->on("endEdit",$endEdit);
		$form->on("validate", "\$('#".$identifier."').form('submit');".$validate);
		$this->jquery->execAtLast("$('form').find('input[type=text],textarea,select').filter(':visible:first').focus();");
		return $form;
	}
	
	private function setFormFields_(&$fields,$relFields){
		$hasRelations=false;
		$relFields=array_flip($relFields);
		foreach ($fields as $index=>$field){
			if(isset($relFields[$field])){
				$fields[$index]=$relFields[$field];
				$hasRelations=true;
			}
		}
		return $hasRelations;
	}
	
	/**
	 * Returns an associative array defining form message title with keys "icon","message","subMessage"
	 * @param DataForm $form
	 * @param object $instance
	 * @return array the message title
	 */
	protected function getFormTitle($form,$instance){
		$type = ($instance->_new) ? "new" : "edit";
		$messageInfos = [ "new" => [ "icon" => HtmlIconGroups::corner ( "table", "plus", "big" ),"subMessage" => "New object creation" ],"edit" => [ "icon" => HtmlIconGroups::corner ( "table", "edit", "big" ),"subMessage" => "Editing an existing object" ] ];
		$message = $messageInfos [$type];
		$message["message"]=\get_class ( $instance );
		return $message;
	}
	
	/**
	 * Sets the components for each field
	 * @param DataForm $form
	 * @param array $fieldTypes associative array of field names (keys) and types (values)
	 */
	public function setFormFieldsComponent(DataForm $form,$fieldTypes){
		$this->setFormFieldsComponent_($form, $fieldTypes);
	}
	
	/**
	 * Sets the components for each field
	 * @param DataForm $form
	 * @param array $fieldTypes associative array of field names (keys) and types (values)
	 */
	public function setMemberFormFieldsComponent(DataForm $form,$fieldTypes){
		$this->setFormFieldsComponent_($form, $fieldTypes);
	}
	
	private function setFormFieldsComponent_(DataForm $form,$fieldTypes){
		foreach ( $fieldTypes as $property => $type ) {
			switch ($property) {
				case "password":
					$form->fieldAsInput ( $property, [ "inputType" => "password" ] );
					$form->setValidationParams(["inline"=>true]);
					break;
				case "email": case "mail":
					$form->fieldAsInput ( $property, [ "inputType" => "email" ,"rules"=>[["email"]]] );
					break;
			}
			
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
	
	/**
	 * Returns a DataElement object for displaying the instance
	 * Used in the display method of the CrudController
	 * in display route
	 * @param object $instance
	 * @param string $model The model class name (long name)
	 * @param boolean $modal
	 * @return \Ajax\semantic\widgets\dataelement\DataElement
	 */
	public function getModelDataElement($instance,$model,$modal){
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
		$this->addEditMemberFonctionality("dataElement");
		return $dataElement;
	}
	
	/**
	 * Returns the captions for DataElement fields
	 * in display route
	 * @param array $captions
	 * @param string $className
	 */
	public function getElementCaptions($captions, $className, $instance) {
		return array_map ( "ucfirst", $captions );
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
	public function getModelDataTable($instances, $model,$totalCount,$page=1) {
		$adminRoute = $this->controller->_getBaseRoute ();
		$files=$this->controller->_getFiles();
		$dataTable = $this->getDataTableInstance( $instances,$model,$totalCount,$page );
		$attributes = $this->controller->_getAdminData()->getFieldNames ( $model );
		$this->setDataTableAttributes($dataTable, $attributes, $model,$instances);
		$dataTable->setCaptions ( $this->getCaptions ( $attributes, $model ) );
	
		$dataTable->addClass ( "small very compact" );
		$lbl=new HtmlLabel("search-query","<span id='search-query-content'></span>");
		$icon=$lbl->addIcon("delete",false);
		$lbl->wrap("<span>","</span>");
		$lbl->setProperty("style", "display: none;");
		$icon->getOnClick($adminRoute.$files->getRouteRefreshTable(),"#lv",["jqueryDone"=>"replaceWith","hasLoader"=>"internal"]);

		$dataTable->addItemInToolbar($lbl);
		$dataTable->addSearchInToolbar();
		$dataTable->setToolbarPosition(PositionInTable::FOOTER);
		$dataTable->getToolbar()->setSecondary();
		return $dataTable;
	}
	
	public function setDataTableAttributes(DataTable $dataTable,$attributes,$model,$instances,$selector=null){
		$modal = ($this->isModal ( $instances, $model ) ? "modal" : "no");
		
		$adminRoute = $this->controller->_getBaseRoute ();
		$files=$this->controller->_getFiles();
		$dataTable->setButtons($this->getDataTableRowButtons());
		$dataTable->setFields ( $attributes );
		if(array_search("password", $attributes)!==false){
			$dataTable->setValueFunction("password", function($v){return UString::mask($v);});
		}
		$dataTable->setIdentifierFunction ( CRUDHelper::getIdentifierFunction ( $model ) );

		if(!isset($selector)){
			if($this->showDetailsOnDataTableClick()){
				$dataTable->getOnRow ( "click", $adminRoute .$files->getRouteDetails(), "#table-details", [ "selector"=>$selector,"attr" => "data-ajax","hasLoader"=>false,"jsCondition"=>"!event.detail || event.detail==1","jsCallback"=>"return false;"] );
				$dataTable->setActiveRowSelector ( "active" );
			}
			
			$dataTable->setUrls ( [ "refresh"=>$adminRoute . $files->getRouteRefresh(),"delete" => $adminRoute . $files->getRouteDelete(),"edit" => $adminRoute . $files->getRouteEdit()."/" . $modal ,"display"=> $adminRoute.$files->getRouteDisplay()."/".$modal] );
			$dataTable->setTargetSelector ( [ "delete" => "#table-messages","edit" => "#frm-add-update" ,"display"=>"#table-details" ] );
			$this->addEditMemberFonctionality("dataTable");
		}
		$this->addAllButtons($dataTable, $attributes);
	}
	
	public function addEditMemberFonctionality($part){
		if(($editMemberParams=$this->getEditMemberParams())!==false){
			if(isset($editMemberParams[$part])){
				$params=$editMemberParams[$part];
				$params->compile($this->controller->_getBaseRoute (),$this->jquery,$part);
			}
		}
	}
	
	/**
	 * @param string $model The model class name (long name)
	 * @param number $totalCount The total count of objects
	 * @return void|number default : 6
	 */
	public function recordsPerPage($model,$totalCount=0){
		if($totalCount>6)
			return 6;
		return ;
	}
	
	/**
	 * Returns the fields on which a grouping is performed
	 */
	public function getGroupByFields(){
		return;
	}
	
	/**
	 * For doing something when $field is generated in form
	 * @param mixed $field
	 */
	public function onGenerateFormField($field){
		return;
	}
	
	
	/**
	 * Returns the dataTable instance for dispaying a list of object
	 * @param array $instances
	 * @param string $model
	 * @param number $totalCount
	 * @param number $page
	 * @return DataTable
	 */
	protected function getDataTableInstance($instances,$model,$totalCount,$page=1):DataTable{
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
		$dataTable->onPreCompile ( function () use (&$dataTable) {
			$dataTable->getHtmlComponent ()->colRightFromRight ( 0 );
		} );
		$dataTable->addAllButtons( false, [ "ajaxTransition" => "random" ], function ($bt) {
			$bt->addClass ( "circular" );
			$this->onDataTableRowButton($bt);
		}, function ($bt) {
			$bt->addClass ( "circular" );
			$this->onDataTableRowButton($bt);
		}, function ($bt) {
			$bt->addClass ( "circular" );
			$this->onDataTableRowButton($bt);
		});
		$dataTable->setDisplayBehavior(["jsCallback"=>'$("#dataTable").hide();',"ajaxTransition"=>"random"]);
	}
	
	/**
	 * To override for modifying the dataTable row buttons
	 * @param HtmlButton $bt
	 */
	public function onDataTableRowButton(HtmlButton $bt){
		
	}
	
	/**
	 * To override for modifying the showConfMessage dialog buttons
	 * @param HtmlButton $confirmBtn The confirmation button
	 * @param HtmlButton $cancelBtn The cancellation button
	 */
	public function onConfirmButtons(HtmlButton $confirmBtn,HtmlButton $cancelBtn){
		
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
					$this->onDisplayFkElementListDetails($item, $memberFK, $fkClass, $oItem);
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
	public function onDisplayFkElementListDetails($element, $member, $className, $object) {
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
	
	protected function relationMembersInForm($form, $instance, $className,$fields,$relations) {
		foreach ( $relations as $field=>$member ) {
			if(array_search($field,$fields)!==false){
				if (OrmUtils::getAnnotationInfoMember ( $className, "#manyToOne", $member ) !== false) {
					$this->manyToOneFormField ( $form, $member, $className, $instance );
				} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $className, "#oneToMany", $member )) !== false) {
					$this->oneToManyFormField ( $form, $member, $instance, $annot );
				} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $className, "#manyToMany", $member )) !== false) {
					$this->manyToManyFormField ( $form, $member, $instance, $annot);
				}
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
				}
				$form->fieldAsDropDown ( $fkField, JArray::modelArray ( $this->controller->_getAdminData ()->getManyToOneDatas( $fkClass, $instance, $member ), $fkIdGetter, "__toString" ) );
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
		$ids = \array_map ( function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter} ();
		}, $fkInstances );
		$instance->{$newField} = \implode ( ",", $ids );
		$form->fieldAsDropDown ( $newField, JArray::modelArray ( $this->controller->_getAdminData ()->getOneToManyDatas ( $fkClass, $instance, $member ), $fkIdGetter, "__toString" ), true );
		$form->setCaption ( $newField, \ucfirst ( $member ) );
	}
	
	protected function manyToManyFormField(DataForm $form, $member, $instance, $annot) {
		$newField = $member . "Ids";
		$fkClass = $annot ["targetEntity"];
		$fkId = OrmUtils::getFirstKey ( $fkClass );
		$fkIdGetter = "get" . \ucfirst ( $fkId );
		$fkInstances = DAO::getManyToMany ( $instance, $member );
		$ids = \array_map ( function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter} ();
		}, $fkInstances );
			$instance->{$newField} = \implode ( ",", $ids );
			$form->fieldAsDropDown ( $newField, JArray::modelArray ( $this->controller->_getAdminData ()->getManyToManyDatas ( $fkClass, $instance, $member ), $fkIdGetter, "__toString" ), true, [ "jsCallback" => function ($elm) {
				$elm->getField ()->asSearch ();
			} ] );
		$form->setCaption ( $newField, \ucfirst ( $member ) );
	}
	
	/**
	 * @return \Ubiquity\controllers\crud\EditMemberParams[]
	 */
	public function getEditMemberParams(){
		return $this->defaultEditMemberParams();
	}
	
	/**
	 * @param string $part
	 * @return \Ubiquity\controllers\crud\EditMemberParams
	 */
	protected function getEditMemberParams_($part){
		$params=$this->getEditMemberParams();
		if($params && isset($params[$part])){
			return $params[$part];
		}
		return new EditMemberParams();
	}
	
	/**
	 * @return \Ubiquity\controllers\crud\EditMemberParams[]
	 */
	protected function defaultEditMemberParams(){
		return ["dataTable"=>EditMemberParams::dataTable(),"dataElement"=>EditMemberParams::dataElement()];
	}
	
}

