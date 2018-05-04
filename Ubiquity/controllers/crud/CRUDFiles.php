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
}

