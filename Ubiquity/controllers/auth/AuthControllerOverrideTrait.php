<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\http\USession;

trait AuthControllerOverrideTrait {
	
	abstract public function badLogin();
	
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
	 * @param object $connected
	 */
	abstract protected function onConnect($connected);
	
	/**
	 * To override for defining a new action when creditentials are invalid
	 */
	protected function onBadCreditentials(){
		$this->badLogin();
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
	 * return boolean true if activeUser is valid
	 */
	abstract public function _isValidUser();
	
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
}

