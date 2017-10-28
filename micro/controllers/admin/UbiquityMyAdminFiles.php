<?php

namespace micro\controllers\admin;

class UbiquityMyAdminFiles {

	public function getAdminBaseRoute(){
		return "Admin";
	}

	public function getViewIndex(){
		return "Admin/index.html";
	}

	public function getViewShowTable(){
		return "Admin/showTable.html";
	}

	public function getViewEditTable(){
		return "Admin/editTable.html";
	}
}
