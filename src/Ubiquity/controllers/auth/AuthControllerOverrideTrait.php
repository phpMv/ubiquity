<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\UCookie;

trait AuthControllerOverrideTrait {
	
	abstract public function badLogin();
	abstract public function bad2FACode();
	
	/**
	 * To override
	 * Return the base route for this Auth controller
	 * @return string
	 */
	public function _getBaseRoute(){
		return ClassUtils::getClassSimpleName(get_class($this));
	}
	
	/**
	 * Processes the data posted by the login form
	 * Have to return the connected user instance
	 */
	abstract protected function _connect();
	
	/**
	 * To override
	 * For creating a new user account.
	 */
	protected function _create(string $login,string $password):?bool{
		return false;
	}
	
	/**
	 * @param object $connected
	 */
	abstract protected function onConnect($connected);
	
	/**
	 * To override for defining a new action when creditentials are invalid.
	 */
	protected function onBadCreditentials(){
		$this->badLogin();
	}
	
	/**
	 * To override for defining a new action when 2FA code is invalid.
	 */
	protected function onBad2FACode(){
		$this->bad2FACode();
	}
	
	/**
	 * To override
	 * Send the 2FA code to the user (email, sms, phone call...)
	 * @param string $code
	 * @param mixed $connected
	 */
	protected function _send2FACode(string $code,$connected){
		
	}
	
	/**
	 * To override
	 * Returns true if the creation of $accountName is possible.
	 * @param string $accountName
	 * @return bool
	 */
	protected function newAccountCreationRule(string $accountName):?bool{
		
	}
	
	/**
	 * To override for defining user session key, default : "activeUser"
	 * @return string
	 */
	public function _getUserSessionKey(){
		return "activeUser";
	}
	
	/**
	 * To override for getting active user, default : USession::get("activeUser")
	 * @return string
	 */
	public function _getActiveUser(){
		return USession::get($this->_getUserSessionKey());
	}
	
	/**
	 * Checks if user is valid for the action
	 * @param string $action
	 * return boolean true if activeUser is valid
	 */
	abstract public function _isValidUser($action=null);
	
	/**
	 * Returns the value from connected user to save it in the cookie for auto connection
	 * @param object $connected
	 */
	protected function toCookie($connected){
		return;
	}
	
	/**
	 * Loads the user from database using the cookie value
	 * @param string $cookie
	 */
	protected function fromCookie($cookie){
		return;
	}
	
	
	/**
	 * Saves the connected user identifier in a cookie
	 * @param object $connected
	 */
	protected function rememberMe($connected){
		$id= $this->toCookie($connected);
		if(isset($id)){
			UCookie::set($this->_getUserSessionKey(),$id);
		}
	}
	
	/**
	 * Returns the cookie for auto connection
	 * @return NULL|string
	 */
	protected function getCookieUser(){
		return UCookie::get($this->_getUserSessionKey());
	}
	
	/**
	 * To override for changing view files
	 * @return AuthFiles
	 */
	protected function getFiles ():AuthFiles{
		return new AuthFiles();
	}
}

