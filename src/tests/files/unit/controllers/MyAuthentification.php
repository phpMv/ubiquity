<?php
namespace controllers;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;
use Ubiquity\attributes\items\router\Route;

/**
 * Class MyAuthentification
 * @package controllers
 * @route("auth","inherited"=>true,"automated"=>true)
 */
#[Route(path: "/auth",inherited: true,automated: true)]
class MyAuthentification extends \Ubiquity\controllers\auth\AuthControllerConfig{

	protected function onConnect($connected) {
		$urlParts=$this->getOriginalURL();
		USession::set($this->_getUserSessionKey(), $connected);
		if(isset($urlParts)){
			$this->_forward(implode("/",$urlParts));
		}else{
			header('location: /TestMainControllerWithAuth');
		}
	}

	protected function _connect() {
		if(URequest::isPost()){
			$email=URequest::post($this->_getLoginInputName());
			$password=URequest::post($this->_getPasswordInputName());
			if($email==='myaddressmail@gmail.com' && $password==='0000') {
				return $email;
			}
		}
		return;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\auth\AuthController::isValidUser()
	 */
	public function _isValidUser($action=null): bool {
		return USession::exists($this->_getUserSessionKey());
	}

	public function _getBaseRoute(): string {
		return '/auth';
	}
	
	protected function getConfigFilename(): string {
		return 'myAuthentification';
	}

	protected function _newAccountCreationRule(string $accountName): ?bool {
		return $accountName!=="myaddressmail@gmail.com";
	}

	public function _create(string $login, string $password): ?bool {
		return $login!='';
	}

	public function hasEmailValidation(): bool {
		return true;
	}

	protected function _sendEmailValidation(string $email, string $validationURL, string $expire): void {
		echo "<a id='url' href='$validationURL'>Validation url</a>";
	}

	protected function _sendEmailAccountRecovery(string $email, string $validationURL, string $expire): bool {
		echo $email;
		echo "<a id='url' href='$validationURL'>Validation url</a>";
		return true;
	}

	protected function passwordResetAction(string $email,string $newPasswordHash):bool{
		echo $email;
		var_dump($newPasswordHash);
		return true;
	}
}
