<?php

namespace Ubiquity\controllers\crud\viewers\traits;

use Ajax\semantic\html\collections\form\HtmlFormField;
use Ajax\semantic\html\collections\form\HtmlFormInput;
use Ajax\semantic\html\elements\HtmlButton;
use Ajax\semantic\html\elements\HtmlIconGroups;
use Ajax\semantic\widgets\dataform\DataForm;
use Ajax\service\JArray;
use Ubiquity\controllers\crud\EditMemberParams;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\Reflexion;

/**
 * Associated with a CRUDController class (part of ModelViewer)
 * Responsible of the display of the form
 * Ubiquity\controllers\crud\viewers\traits$FormModelViewerTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 * @property \Ajax\JsUtils $jquery
 */
trait FormModelViewerTrait {

	protected function relationMembersInForm($form, $instance, $className, $fields, $relations) {
		foreach ( $relations as $field => $member ) {
			if (array_search ( $field, $fields ) !== false) {
				if (OrmUtils::getAnnotationInfoMember ( $className, "#manyToOne", $member ) !== false) {
					$this->manyToOneFormField ( $form, $member, $className, $instance );
				} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $className, "#oneToMany", $member )) !== false) {
					$this->oneToManyFormField ( $form, $member, $instance, $annot );
				} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $className, "#manyToMany", $member )) !== false) {
					$this->manyToManyFormField ( $form, $member, $instance, $annot );
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
				$form->fieldAsDropDown ( $fkField, JArray::modelArray ( $this->controller->_getAdminData ()->getManyToOneDatas ( $fkClass, $instance, $member ), $fkIdGetter, "__toString" ) );
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
	 *
	 * @return \Ubiquity\controllers\crud\EditMemberParams[]
	 */
	public function getEditMemberParams() {
		return $this->defaultEditMemberParams ();
	}

	/**
	 *
	 * @param string $part
	 * @return \Ubiquity\controllers\crud\EditMemberParams
	 */
	protected function getEditMemberParams_($part) {
		$params = $this->getEditMemberParams ();
		if ($params && isset ( $params [$part] )) {
			return $params [$part];
		}
		return new EditMemberParams ();
	}

	/**
	 *
	 * @return \Ubiquity\controllers\crud\EditMemberParams[]
	 */
	protected function defaultEditMemberParams() {
		return [ "dataTable" => EditMemberParams::dataTable (),"dataElement" => EditMemberParams::dataElement () ];
	}

	/**
	 * Returns the form for adding or modifying an object
	 *
	 * @param string $identifier
	 * @param object $instance the object to add or modify
	 * @return \Ajax\semantic\widgets\dataform\DataForm
	 */
	public function getForm($identifier, $instance) {
		$form = $this->jquery->semantic ()->dataForm ( $identifier, $instance );
		$form->setLibraryId ( "frmEdit" );
		$className = \get_class ( $instance );
		$fields = array_unique ( $this->controller->_getAdminData ()->getFormFieldNames ( $className, $instance ) );
		$relFields = OrmUtils::getFieldsInRelations_ ( $className );

		$this->setFormFields_ ( $fields, $relFields );
		array_unshift ( $fields, "_message" );
		$form->setFields ( $fields );

		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		$this->setFormFieldsComponent ( $form, $fieldTypes );
		$this->relationMembersInForm ( $form, $instance, $className, $fields, $relFields );
		OrmUtils::setFieldToMemberNames ( $fields, $relFields );
		$form->setCaptions ( $this->getFormCaptions ( $fields, $className, $instance ) );
		$message = $this->getFormTitle ( $form, $instance );
		$form->setCaption ( "_message", $message ["subMessage"] );
		$form->fieldAsMessage ( "_message", [ "icon" => $message ["icon"] ] );
		$instance->_message = $message ["message"];
		$form->setSubmitParams ( $this->controller->_getBaseRoute () . "/_updateModel", "#frm-add-update" );
		$form->onGenerateField ( [ $this,'onGenerateFormField' ] );
		return $form;
	}

	/**
	 * Returns a form for member editing
	 *
	 * @param string $identifier
	 * @param object $instance
	 * @param string $member
	 * @param string $td
	 * @param string $part
	 * @return \Ajax\semantic\widgets\dataform\DataForm
	 */
	public function getMemberForm($identifier, $instance, $member, $td, $part) {
		$editMemberParams = $this->getEditMemberParams_ ( $part );

		$form = $this->jquery->semantic ()->dataForm ( $identifier, $instance );
		$form->on ( "dblclick", "", true, true );
		$form->setProperty ( "onsubmit", "return false;" );
		$form->addClass ( "_memberForm" );
		$className = \get_class ( $instance );
		$fields = [ "id",$member ];
		$relFields = OrmUtils::getFieldsInRelations_ ( $className );
		$hasRelations = $this->setFormFields_ ( $fields, $relFields );
		$form->setFields ( $fields );
		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		$form->fieldAsHidden ( 0 );
		$this->setMemberFormFieldsComponent ( $form, $fieldTypes );
		if ($hasRelations) {
			$this->relationMembersInForm ( $form, $instance, $className, $fields, $relFields );
		}
		$form->setCaptions ( [ "","" ] );
		$form->onGenerateField ( function (HtmlFormField $f, $nb) use ($identifier, $editMemberParams) {
			if ($nb == 1) {
				$f->setSize ( "mini" );
				if ($editMemberParams->getHasButtons ()) {
					$btO = HtmlButton::icon ( "btO", "check" )->addClass ( "green mini compact" )->onClick ( "\$('#" . $identifier . "').trigger('validate');", true, true );
					$btC = HtmlButton::icon ( "btC", "close" )->addClass ( "mini compact" )->onClick ( "\$('#" . $identifier . "').trigger('endEdit');" );
					$f->wrap ( "<div class='fields' style='margin:0;'>", [ $btO,$btC,"</div>" ] );
					if (! $editMemberParams->getHasPopup ()) {
						$f->setWidth ( 16 )->setProperty ( "style", "padding-left:0;" );
					}
				}
				$f->on ( "keydown", "if(event.which == 13) {\$('#" . $identifier . "').trigger('validate');}if(event.keyCode===27) {\$('#" . $identifier . "').trigger('endEdit');}" );
				$f->onClick ( "return false;", true, true );
			} else {
				$f->setProperty ( "style", "display: none;" );
			}
		} );
		$form->setSubmitParams ( $this->controller->_getBaseRoute () . "/_updateMember/" . $member . "/" . $editMemberParams->getUpdateCallback (), "#" . $td, [ "attr" => "","hasLoader" => false,"jsCallback" => "$(self).remove();","jqueryDone" => "html" ] );
		if ($editMemberParams->getHasPopup ()) {
			$endEdit = "\$('#" . $identifier . "').html();\$('.popup').hide();\$('#" . $td . "').popup('destroy');";
			$validate = $endEdit;
		} else {
			$endEdit = "let td=\$('#" . $td . "');td.html(td.data('originalText'));";
			$validate = "";
		}
		$form->on ( "endEdit", $endEdit );
		$form->on ( "validate", "\$('#" . $identifier . "').form('submit');" . $validate );
		$this->jquery->execAtLast ( "$('form').find('input[type=text],textarea,select').filter(':visible:first').focus();" );
		return $form;
	}

	private function setFormFields_(&$fields, $relFields) {
		$hasRelations = false;
		$relFields = array_flip ( $relFields );
		foreach ( $fields as $index => $field ) {
			if (isset ( $relFields [$field] )) {
				$fields [$index] = $relFields [$field];
				$hasRelations = true;
			}
		}
		return $hasRelations;
	}

	/**
	 * Returns an associative array defining form message title with keys "icon","message","subMessage"
	 *
	 * @param DataForm $form
	 * @param object $instance
	 * @return array the message title
	 */
	protected function getFormTitle($form, $instance) {
		$type = ($instance->_new) ? "new" : "edit";
		$messageInfos = [ "new" => [ "icon" => HtmlIconGroups::corner ( "table", "plus", "big" ),"subMessage" => "New object creation" ],"edit" => [ "icon" => HtmlIconGroups::corner ( "table", "edit", "big" ),"subMessage" => "Editing an existing object" ] ];
		$message = $messageInfos [$type];
		$message ["message"] = \get_class ( $instance );
		return $message;
	}

	/**
	 * Sets the components for each field
	 *
	 * @param DataForm $form
	 * @param array $fieldTypes associative array of field names (keys) and types (values)
	 */
	public function setFormFieldsComponent(DataForm $form, $fieldTypes) {
		$this->setFormFieldsComponent_ ( $form, $fieldTypes );
	}

	/**
	 * Sets the components for each field
	 *
	 * @param DataForm $form
	 * @param array $fieldTypes associative array of field names (keys) and types (values)
	 */
	public function setMemberFormFieldsComponent(DataForm $form, $fieldTypes) {
		$this->setFormFieldsComponent_ ( $form, $fieldTypes );
	}

	protected function setFormFieldsComponent_(DataForm $form, $fieldTypes) {
		foreach ( $fieldTypes as $property => $type ) {
			switch ($property) {
				case "password" :
					$form->fieldAsInput ( $property, [ "inputType" => "password" ] );
					$form->setValidationParams ( [ "inline" => true ] );
					break;
				case "email" :
				case "mail" :
					$form->fieldAsInput ( $property, [ "inputType" => "email","rules" => [ [ "email" ] ] ] );
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
	 * For doing something when $field is generated in form
	 *
	 * @param mixed $field
	 */
	public function onGenerateFormField($field, $nb) {
		if ($field instanceof HtmlFormInput) {
			if ($field->getDataField ()->getProperty ( 'type' ) == "datetime-local") {
				$v = $field->getDataField ()->getProperty ( 'value' );
				$field->getDataField ()->setValue ( date ( "Y-m-d\TH:i:s", strtotime ( $v ) ) );
			}
		}
		return;
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
	 * Returns the captions for form fields
	 *
	 * @param array $captions
	 * @param string $className
	 */
	public function getFormCaptions($captions, $className, $instance) {
		return \array_map ( "ucfirst", $captions );
	}
}

