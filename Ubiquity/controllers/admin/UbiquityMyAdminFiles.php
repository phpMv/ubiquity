<?php

namespace Ubiquity\controllers\admin;

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

	public function getViewRestIndex(){
		return "Admin/rest/index.html";
	}

	public function getViewLogsIndex(){
		return "Admin/logs/index.html";
	}

	public function getViewRestFormTester(){
		return "Admin/rest/formTester.html";
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

	public function getViewClassesDiagram(){
		return "Admin/data/diagClasses.html";
	}

	public function getViewYumlReverse(){
		return "Admin/data/yumlReverse.html";
	}

	public function getViewDatabaseIndex(){
		return "Admin/database/index.html";
	}

	public function getViewDatabaseCreate(){
		return "Admin/database/create.html";
	}

	public function getViewDatasExport(){
		return "Admin/database/export.html";
	}
}
