<?php
%namespace%
%uses%

 /**
 * CRUD Controller %controllerName%
 **/
class %controllerName% extends %baseClass%{

	public function __construct(){
		parent::__construct();
		$this->model="%resource%";
	}

	public function _getBaseRoute() {
		return '%controllerName%';
	}
	
%content%

}
