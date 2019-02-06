<?php
namespace controllers;
 use Ubiquity\controllers\auth\AuthController;
use Ubiquity\controllers\auth\WithAuthTrait;

 /**
 * Controller TestControllerWithAuth
 **/
class TestControllerWithAuth extends ControllerBase{
	use WithAuthTrait;

	public function index(){
		echo "Welcome ".$this->getAuthController()->_getActiveUser()."!";
	}
	
	public function autre(){
		echo "autre!";
	}
	protected function getAuthController(): AuthController {
		return new TestAuthController();
	}

}
