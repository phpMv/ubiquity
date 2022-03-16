<?php

namespace Ubiquity\controllers\crud;

use Ajax\JsUtils;

/**
 * For using in a CRUDController ModelViewer with getEditMemberParams method 
 * @author jc
 *
 */
class EditMemberParams {
	private $parentId;
	private $selector;
	private $event;
	private $hasButtons;
	private $hasPopup;
	private $updateCallback;
	private $identifierSelector;
	
	public function __construct($parentId="",$selector="[data-field]",$event="dblclick",$hasButtons=true,$hasPopup=false,$updateCallback="",$identifierSelector="$(this).closest('tr').attr('data-ajax')"){
		$this->parentId=$parentId;
		$this->selector=$selector;
		$this->event=$event;
		$this->hasButtons=$hasButtons;
		$this->hasPopup=$hasPopup;
		$this->updateCallback=$updateCallback;
		$this->identifierSelector=$identifierSelector;
	}
	
	private function getJsCallbackForEditMember(){
		if($this->hasPopup){
			return "$(self).popup({hideOnScroll: false,exclusive: true,delay:{show:50,hide: 5000},closable: false, variation: 'very wide',html: data, hoverable: true,className: {popup: 'ui popup'}}).popup('show');";
		}else{
			return "$(self).html(function(i,v){return $(this).data('originalText', v) || '';});";
		}
	}
	
	/**
	 * @return string
	 */
	public function getSelector() {
		return $this->selector;
	}

	/**
	 * @return string
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * @return boolean
	 */
	public function getHasButtons() {
		return $this->hasButtons;
	}

	/**
	 * @return boolean
	 */
	public function getHasPopup() {
		return $this->hasPopup;
	}

	/**
	 * @return string
	 */
	public function getUpdateCallback() {
		return $this->updateCallback;
	}

	/**
	 * @param string $selector
	 */
	public function setSelector($selector) {
		$this->selector = $selector;
	}

	/**
	 * @param string $event
	 */
	public function setEvent($event) {
		$this->event = $event;
	}

	/**
	 * @param boolean $hasButtons
	 */
	public function setHasButtons($hasButtons) {
		$this->hasButtons = $hasButtons;
	}

	/**
	 * @param boolean $hasPopup
	 */
	public function setHasPopup($hasPopup) {
		$this->hasPopup = $hasPopup;
	}

	/**
	 * @param string $updateCallback
	 */
	public function setUpdateCallback($updateCallback) {
		$this->updateCallback = $updateCallback;
	}
	/**
	 * @return mixed
	 */
	public function getIdentifierSelector() {
		return $this->identifierSelector;
	}

	/**
	 * @param mixed $identifierSelector
	 */
	public function setIdentifierSelector($identifierSelector) {
		$this->identifierSelector = $identifierSelector;
	}
	
	public static function dataElement(){
		return new EditMemberParams("#de", "td[data-field]","dblclick",true,true,"updateMemberDataElement","$(this).closest('table').attr('data-ajax')");
	}
	
	public static function dataTable($parentId='#lv'){
		return new EditMemberParams($parentId);
	}
	
	public function compile($baseRoute,JsUtils $jquery,$part=null){
		$part=(isset($part))?", part: '".$part."'":"";
		$jsCallback=$this->getJsCallbackForEditMember();
		$element=null;
		if(!$this->hasPopup){
			$element="\$(self)";
			$before=$jsCallback;
			$jsCallback="";
		}else{
			$before="";
		}
		$jquery->postOn($this->event,$this->selector, $baseRoute."/editMember/","{id: ".$this->identifierSelector.",td:$(this).attr('id')".$part."}",$element,["attr"=>"data-field","hasLoader"=>false,"jqueryDone"=>"html","before"=>"$('._memberForm').trigger('endEdit');".$before,"jsCallback"=>$jsCallback,"listenerOn"=>$this->parentId]);
		
	}
	
}

