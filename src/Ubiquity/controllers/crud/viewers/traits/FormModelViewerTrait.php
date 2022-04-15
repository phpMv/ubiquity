<?php

namespace Ubiquity\controllers\crud\viewers\traits;

use Ajax\php\ubiquity\utils\DataFormHelper;
use Ajax\semantic\html\collections\form\HtmlFormField;
use Ajax\semantic\html\collections\form\HtmlFormInput;
use Ajax\semantic\html\elements\HtmlButton;
use Ajax\semantic\html\elements\HtmlIconGroups;
use Ajax\semantic\widgets\base\FieldAsTrait;
use Ajax\semantic\widgets\dataform\DataForm;
use Ajax\semantic\widgets\datatable\DataTable;
use Ajax\service\JArray;
use normalizer\UserNormalizer;
use Ubiquity\contents\validation\ValidatorsManager;
use Ubiquity\controllers\crud\EditMemberParams;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\Reflexion;
use Ajax\semantic\html\collections\form\HtmlFormCheckbox;

/**
 * Associated with a CRUDController class (part of ModelViewer)
 * Responsible of the display of the form
 * Ubiquity\controllers\crud\viewers\traits$FormModelViewerTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.7
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 */
trait FormModelViewerTrait {
	
	abstract public function getDataTableId();
	
	protected function relationMembersInForm($form, $instance, $className, $fields, $relations, &$fieldTypes) {
		foreach ( $relations as $field => $member ) {
			if (\array_search ( $field, $fields ) !== false) {
				unset($fieldTypes[$field]);
				if (OrmUtils::getAnnotationInfoMember ( $className, '#manyToOne', $member ) !== false) {
					$this->manyToOneFormField ( $form, $member, $className, $instance );
				} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $className, '#oneToMany', $member )) !== false) {
					$fkClass=$annot['className'];
					if(OrmUtils::isManyToMany($fkClass)) {
						$this->oneToManyFormFieldDt($form, $member, $instance, $annot);
					}else{
						$this->oneToManyFormField ( $form, $member, $instance, $annot );
					}
				} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $className, '#manyToMany', $member )) !== false) {
					$this->manyToManyFormField ( $form, $member, $instance, $annot );
				}
			}
		}
	}
	
	protected function manyToOneFormField($form, $member, $className, $instance) {
		$joinColumn = OrmUtils::getAnnotationInfoMember ( $className, '#joinColumn', $member );
		if ($joinColumn) {
			$fkObject = Reflexion::getMemberValue ( $instance, $member );
			$fkClass = $joinColumn ['className'];
			if ($fkObject === null) {
				$fkObject = new $fkClass ();
			}
			$fkId = OrmUtils::getFirstKey ( $fkClass );
			
			$fkIdGetter = 'get' . \ucfirst ( $fkId );
			if (\method_exists ( $fkObject, '__toString' ) && \method_exists ( $fkObject, $fkIdGetter )) {
				$fkField = $joinColumn ['name'];
				$fkValue = OrmUtils::getFirstKeyValue ( $fkObject );
				if (! Reflexion::setMemberValue ( $instance, $fkField, $fkValue )) {
					$instance->{$fkField} = OrmUtils::getFirstKeyValue ( $fkObject );
				}
				$attr = [ ];
				if (OrmUtils::isNullable ( $className, $member )) {
					$attr = [ 'jsCallback' => function ($elm) {
						$elm->getField ()->setClearable ( true );
					} ];
				} else {
					$attr = [ 'jsCallback' => function ($elm) {
						$elm->addRules ( [ 'empty' ] );
					} ];
				}
				$form->fieldAsDropDown ( $fkField, JArray::modelArray ( $this->controller->_getAdminData ()->getManyToOneDatas ( $fkClass, $instance, $member ), $fkIdGetter, '__toString' ), false, $attr );
				$form->setCaption ( $fkField, \ucfirst ( $member ) );
			}
		}
	}
	
	protected function oneToManyFormField(DataForm $form, $member, $instance, $annot) {
		$newField = $member . 'Ids';
		$fkClass = $annot ['className'];
		$fkId = OrmUtils::getFirstKey ( $fkClass );
		$fkIdGetter = 'get' . \ucfirst ( $fkId );
		$fkInstances = Reflexion::getMemberValue ( $instance, $member );//DAO::getOneToMany ( $instance, $member );
		$ids = \array_map ( function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter} ();
		}, $fkInstances );
			$instance->{$newField} = \implode ( ",", $ids );
			$form->fieldAsDropDown ( $newField, JArray::modelArray ( $this->controller->_getAdminData ()->getOneToManyDatas ( $fkClass, $instance, $member ), $fkIdGetter, "__toString" ), true );
			$form->setCaption ( $newField, \ucfirst ( $member ) );
	}

	protected function oneToManyFormFieldDt(DataForm $form,$member, $instance, $annot){
		$newField = $member . 'Ids';
		$fkClass = $annot ['className'];
		$fkv=OrmUtils::getFirstKeyValue($instance);
		if($fkv!=null) {
			$fkInstances = DAO::getOneToMany($instance, $member);
		}
		$fields=OrmUtils::getManyToManyFieldsDt ( \get_class($instance),$fkClass );
		$fkInstances[]=new $fkClass();
		$relFields = OrmUtils::getFieldsInRelations_ ( $fkClass );
		$form->fieldAsDataTable( $newField, $fkClass,$fkInstances,$fields,['jsCallback'=>function(DataTable $dt) use($fkClass,$relFields,$fields,$newField){
			$this->dtManyField($dt,$fkClass,$relFields,$fields);
			$id=$dt->getIdentifier();
			$removeSelected='function updateCmb(){let cmb=$("#'.$id.' tbody tr:last-child div.dropdown");cmb.find("a[data-value]").removeClass("disabled");$("#'.$id.' tbody tr div.dropdown input").each(function(){if($(this).val()) cmb.find("[data-value="+$(this).val()+"]").addClass("disabled");});}';
			$this->jquery->execAtLast($removeSelected.'updateCmb();$("#'.$id.' tbody tr:last-child .dropdown").removeClass("disabled");$("#'.$id.' tbody tr:last-child").find("input._status").val("added");');
			$deleteJS=$this->jquery->execJSFromFile('@framework/js/delete',[],false);
			$this->jquery->click('._delete','$("[name='.$newField.']").val("updated");'.$deleteJS,true,false,false, "#$id");
			$this->jquery->change('tr:last-child input',$this->jquery->execJSFromFile('@framework/js/change',compact('id'),false),false,false,"#$id");
			$this->jquery->change('tr .dropdown input','updateCmb();',false,false,"#$id");
			$this->jquery->change('tr input','$("[name='.$newField.']").val("updated");if($(this).closest("tr").find("input._status").val()!=="added"){$(this).closest("tr").find("input._status").val("updated");}',false,false,"#$id");

		}] );
		$form->setCaption ( $newField, \ucfirst ( $member ) );
	}

	protected function dtManyField(DataTable $dt,$className,array $relations,array $fields){
		foreach ( $relations as $field => $member ) {
			if (\array_search($field, $fields) !== false) {
				if (OrmUtils::getAnnotationInfoMember($className, "#manyToOne", $member) !== false) {
					$this->dtManyFieldCmb($dt,$member,$className);
				}
			}
		}
	}

	protected function dtManyFieldCmb($dt, $member, $className) {
		$joinColumn = OrmUtils::getAnnotationInfoMember ( $className, "#joinColumn", $member );
		if ($joinColumn) {
			$fkClass = $joinColumn ['className'];
			$fkId = OrmUtils::getFirstKey ( $fkClass );

			$fkIdGetter = 'get' . \ucfirst ( $fkId );
			$fkField = $joinColumn ['name'];

			$attr = [ ];
			if (OrmUtils::isNullable ( $className, $member )) {
				$attr = [ 'jsCallback' => function ($elm) {
					$f=$elm->getField ();
					$f->setClearable ( true );
					$f->addClass('disabled');
				} ];
			} else {
				$attr = [ 'jsCallback' => function ($elm) {
					$elm->getField()->addClass('disabled');
				} ];
			}
			$dt->fieldAsDropDown ( $fkField, JArray::modelArray ( $this->controller->_getAdminData ()->getManyToOneDatas ( $fkClass, null, $member ), $fkIdGetter, '__toString' ), false, $attr );
		}
	}
	
	protected function manyToManyFormField(DataForm $form, $member, $instance, $annot) {
		$newField = $member . 'Ids';
		$fkClass = $annot ['targetEntity'];
		$fkId = OrmUtils::getFirstKey ( $fkClass );
		$fkIdGetter = 'get' . \ucfirst ( $fkId );
		$fkInstances = Reflexion::getMemberValue ( $instance, $member );//DAO::getManyToMany ( $instance, $member );
		$ids = \array_map ( function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter} ();
		}, $fkInstances );
			$instance->{$newField} = \implode ( ',', $ids );
			$form->fieldAsDropDown ( $newField, JArray::modelArray ( $this->controller->_getAdminData ()->getManyToManyDatas ( $fkClass, $instance, $member ), $fkIdGetter, '__toString' ), true, [ 'jsCallback' => function ($elm) {
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
	}
	
	/**
	 *
	 * @return \Ubiquity\controllers\crud\EditMemberParams[]
	 */
	protected function defaultEditMemberParams() {
		return [ 'dataTable' => EditMemberParams::dataTable ( '#'.$this->getDataTableId () ),'dataElement' => EditMemberParams::dataElement () ];
	}
	
	/**
	 * Returns the form for adding or modifying an object
	 *
	 * @param string $identifier
	 * @param object $instance the object to add or modify
	 * @param ?string $updateUrl
	 * @return \Ajax\semantic\widgets\dataform\DataForm
	 */
	public function getForm($identifier, $instance, $updateUrl = 'updateModel') {
		$hasMessage=$this->formHasMessage();
		$form = $this->jquery->semantic ()->dataForm ( $identifier, $instance );
		$form->setLibraryId ( 'frmEdit' );
		$className = \get_class ( $instance );
		$fields = \array_unique ( $this->controller->_getAdminData ()->getFormFieldNames ( $className, $instance ) );
		$relFields = OrmUtils::getFieldsInRelations_ ( $className );
		
		$this->setFormFields_ ( $fields, $relFields );
		if($hasMessage) {
			\array_unshift($fields, '_message');
		}
		$form->setFields ( $fields );
		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		$attrs=ValidatorsManager::getUIConstraints($instance);

		$this->relationMembersInForm ( $form, $instance, $className, $fields, $relFields ,$fieldTypes);
		OrmUtils::setFieldToMemberNames ( $fields, $relFields );
		$form->setCaptions ( $this->getFormCaptions ( $fields, $className, $instance ) );
		if($hasMessage) {
			$message = $this->getFormTitle($form, $instance);
			$form->setCaption('_message', $message ['subMessage']);
			$form->fieldAsMessage('_message', ['icon' => $message ['icon']]);
			$instance->_message = $message ['message'];
		}

		$this->setFormFieldsComponent ( $form, $fieldTypes,$attrs);
		$form->setSubmitParams ( $this->controller->_getBaseRoute () . '/' . $updateUrl, '#frm-add-update' );
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
	 * @param ?string $updateUrl
	 * @return \Ajax\semantic\widgets\dataform\DataForm
	 */
	public function getMemberForm($identifier, $instance, $member, $td, $part, $updateUrl = '_updateMember') {
		$editMemberParams = $this->getEditMemberParams_ ( $part );
		
		$form = $this->jquery->semantic ()->dataForm ( $identifier, $instance );
		$form->on ( "dblclick", "", true, true );
		$form->setProperty ( "onsubmit", "return false;" );
		$form->setProperty('style','margin:0;');
		$form->addClass ( "_memberForm" );
		$className = \get_class ( $instance );
		$fields = [ "id",$member ];
		$relFields = OrmUtils::getFieldsInRelations_ ( $className );
		$hasRelations = $this->setFormFields_ ( $fields, $relFields );
		$form->setFields ( $fields );
		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		$form->fieldAsHidden ( 0 );
		$attrs=ValidatorsManager::getUIConstraints($instance);
		$this->setMemberFormFieldsComponent ( $form, $fieldTypes ,$attrs);
		if ($hasRelations) {
			$this->relationMembersInForm ( $form, $instance, $className, $fields, $relFields,$fieldTypes );
		}
		$form->setCaptions ( [ "","" ] );
		$form->onGenerateField ( function (HtmlFormField $f, $nb) use ($identifier, $editMemberParams) {
			if ($nb == 1) {
				$f->setSize ( "mini" );
				if ($editMemberParams->getHasButtons ()) {
					$btO = HtmlButton::icon ( "btO", "check" )->addClass ( "green mini compact" )->onClick ( "\$('#" . $identifier . "').trigger('validate');", true, true );
					$btC = HtmlButton::icon ( "btC", "close" )->addClass ( "mini compact" )->onClick ( "\$('#" . $identifier . "').trigger('endEdit');" );
					$f->wrap ( "<div class='inline fields' style='margin:0;'>", [ $btO,$btC,"</div>" ] );
					if (! $editMemberParams->getHasPopup ()) {
						if (! ($f instanceof HtmlFormCheckbox)) {
							$f->setWidth ( 16 )->setProperty ( "style", "padding-left:0;" );
						}
					}
				}
				$f->on ( "keydown", "if(event.keyCode===27) {\$('#" . $identifier . "').trigger('endEdit');}" );
				$f->onClick ( "return false;", true, true );
			} else {
				$f->setProperty ( "style", "display: none;" );
			}
		} );
			$form->setSubmitParams ( $this->controller->_getBaseRoute () . "/$updateUrl/" . $member . "/" . $editMemberParams->getUpdateCallback (), "#" . $td, [ "attr" => "","hasLoader" => false,"jsCallback" => "$(self).remove();","jqueryDone" => "html" ] );
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
		$relFields = \array_flip ( $relFields );
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
		$type = ($instance->_new) ? 'new' : 'edit';
		$messageInfos = [ 'new' => [ 'icon' => HtmlIconGroups::corner ( 'table ' . $this->style, 'plus ' . $this->style, 'big' ),'subMessage' => '&nbsp;New object creation' ],'edit' => [ 'icon' => HtmlIconGroups::corner ( 'table ' . $this->style, 'edit ' . $this->style, 'big' ),'subMessage' => '&nbsp;Editing an existing object' ] ];
		$message = $messageInfos [$type];
		$message ['message'] = '&nbsp;' . \get_class ( $instance );
		return $message;
	}
	
	/**
	 * Sets the components for each field
	 *
	 * @param DataForm $form
	 * @param array $fieldTypes associative array of field names (keys) and types (values)
	 * @param ?array $attributes
	 */
	public function setFormFieldsComponent(DataForm $form, $fieldTypes, $attributes = [ ]) {
		DataFormHelper::addDefaultUIConstraints($form,$fieldTypes,$attributes);
	}
	
	/**
	 * Sets the components for each field
	 *
	 * @param DataForm $form
	 * @param array $fieldTypes associative array of field names (keys) and types (values)
	 * @param array $attributes
	 */
	public function setMemberFormFieldsComponent(DataForm $form, $fieldTypes,$attributes=[]) {
		DataFormHelper::addDefaultUIConstraints($form,$fieldTypes,$attributes);
	}
	
	/**
	 * For doing something when $field is generated in form
	 *
	 * @param mixed $field
	 */
	public function onGenerateFormField($field, $nb,$name) {
		if ($field instanceof HtmlFormInput) {
			if ($field->getDataField ()->getProperty ( 'type' ) == "datetime-local") {
				$v = $field->getDataField ()->getProperty ( 'value' );
				$field->getDataField ()->setValue ( date ( "Y-m-d\TH:i:s", \strtotime ( $v ) ) );
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
		return \array_map ( 'ucfirst', $captions );
	}
	
	/**
	 * Returns the modal Title.
	 * @param object $instance
	 * @return string
	 */
	public function getFormModalTitle(object $instance):string{
		return \get_class ( $instance );
	}
	
	/**
	 * If true, display a message for editing or updating (default true).
	 * @return bool
	 */
	public function formHasMessage():bool{
		return true;
	}
	
	/**
	 * Hook for changing the edit/new modal buttons.
	 * @param HtmlButton $btOkay
	 * @param HtmlButton $btCancel
	 */
	public function onFormModalButtons(HtmlButton $btOkay,HtmlButton $btCancel):void{
		$btOkay->setValue ( "Validate modifications" );
	}
}

