<?php
namespace controllers\crud\files;

use Ubiquity\controllers\crud\CRUDFiles;
 /**
 * Class TestCrudOrgasFiles
 **/
class TestCrudOrgasFiles extends CRUDFiles{
	public function getViewIndex(){
		return "TestCrudOrgas/index.html";
	}

	public function getViewForm(){
		return "TestCrudOrgas/form.html";
	}

	public function getViewDisplay(){
		return "TestCrudOrgas/display.html";
	}


}
