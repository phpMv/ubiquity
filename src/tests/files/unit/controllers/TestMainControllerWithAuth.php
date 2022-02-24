<?php
namespace controllers;
 use Ubiquity\controllers\auth\AuthController;
 use Ubiquity\controllers\auth\WithAuthTrait;

 /**
  * Controller TestMainControllerWithAuth
  */
class TestMainControllerWithAuth extends \controllers\ControllerBase{
use WithAuthTrait;


	public function index(){
		$this->loadView("TestMainControllerWithAuth/index.html");
	}

	protected function getAuthController(): AuthController {
		return new MyAuthentification($this);
	}

	public function test(){
		echo "test ok!";
	}


}
