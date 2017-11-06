<?php

namespace micro\controllers\admin;

class UbiquityMyAdminFiles {

	public function getAdminBaseRoute(){
		return "Admin";
	}

	public function getViewDataIndex(){
		return "Admin/data/index.html";
	}

	public function getViewRoutesIndex(){
		return "Admin/routes/index.html";
	}

	public function getViewCacheIndex(){
		return "Admin/cache/index.html";
	}

	public function getViewControllersIndex(){
		return "Admin/controllers/index.html";
	}

	public function getViewConfigIndex(){
		return "Admin/config/index.html";
	}

	public function getViewIndex(){
		return "Admin/index.html";
	}

	public function getViewShowTable(){
		return "Admin/data/showTable.html";
	}

	public function getViewEditTable(){
		return "Admin/data/editTable.html";
	}

	public function getViewHeader(){
		return "Admin/main/vHeader.html";
	}
}
