<?php
namespace controllers;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;
use controllers\auth\files\TestAuthControllerFiles;
use Ubiquity\controllers\auth\AuthFiles;
use Ubiquity\orm\DAO;
use models\User;

 /**
 * Auth Controller TestAuthController
 **/
class TestAuthController extends \Ubiquity\controllers\auth\AuthController{

	protected function onConnect($connected) {
		$urlParts=$this->getOriginalURL();
		USession::set($this->_getUserSessionKey(), $connected);
		if(isset($urlParts)){
			$this->_forward(implode("/",$urlParts));
		}else{
			$this->_forward("/TestControllerWithAuth");
		}
	}

	protected function _connect() {
		if(URequest::isPost()){
			$email=URequest::post($this->_getLoginInputName());
			$password=URequest::post($this->_getPasswordInputName());
			return DAO::uGetOne(User::class, "email = ? and password = ?",false,[$email,$password]);
		}
		return;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\auth\AuthController::isValidUser()
	 */
	public function _isValidUser($action=null):bool {
		return USession::exists($this->_getUserSessionKey());
	}

	public function _getBaseRoute():string {
		return 'TestAuthController';
	}
	
	protected function getFiles(): AuthFiles{
		return new TestAuthControllerFiles();
	}

	public function _getLoginInputName():string{
		return "email";
	}

}
