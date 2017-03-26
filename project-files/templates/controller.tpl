<?php
use micro\utils\RequestUtils;
use micro\controllers\Controller;
 /**
 * Controller %controllerName%
 **/
class %controllerName% extends Controller{

	public function initialize(){
		if(!RequestUtils::isAjax()){
			$this->loadView("main/vHeader.html");
		}
	}

	public function index(){%indexContent%}
	
	public function finalize(){
		if(!RequestUtils::isAjax()){
			$this->loadView("main/vFooter.html");
		}
	}
}