<?php

namespace Ubiquity\controllers\crud;

class CRUDFiles {
	private $viewBase="@framework/crud";
	
	public function getViewDataIndex() {
		return $this->viewBase."/index.html";
	}
	
	public function getViewShowTable() {
		return $this->viewBase."/showTable.html";
	}
	
	public function getViewEditTable() {
		return $this->viewBase."/editTable.html";
	}
}

