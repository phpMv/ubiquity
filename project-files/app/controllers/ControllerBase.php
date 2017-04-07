<?php
namespace controllers;
use micro\utils\RequestUtils;
use micro\controllers\Controller;
 /**
 * ControllerBase
 **/
abstract class ControllerBase extends Controller{

	public function initialize(){
		if(!RequestUtils::isAjax()){
			$this->loadView("main/vHeader.html");
		}
	}

	public function finalize(){
		if(!RequestUtils::isAjax()){
			$this->loadView("main/vFooter.html");
		}
	}
}
