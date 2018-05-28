<?php

namespace Ubiquity\controllers\crud;

class CRUDFiles {
	protected $viewBase;
	
	public function __construct(){
		$this->viewBase="@framework/crud";
	}
	
	public function getViewIndex() {
		return $this->viewBase."/index.html";
	}
	
	public function getViewForm() {
		return $this->viewBase."/form.html";
	}
	
	public function getViewDisplay() {
		return $this->viewBase."/display.html";
	}
	
	/**
	 * Returns the base template for all Auth actions if getBaseTemplate return a base template filename
	 * @return string
	 */
	public function getViewBaseTemplate() {
		return $this->viewBase."/baseTemplate.html";
	}
	
	/**
	 * To override
	 * Returns the base template filename, default : null
	 */
	public function getBaseTemplate(){
		return;
	}
}

