<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\UCookie;

/**
 * Trait AuthControllerOverrideTrait
 *
 * @property string $TOKENS_VALIDATE_EMAIL
 * @property string $TOKENS_RECOVERY_ACCOUNT
 */
trait AuthControllerOverrideTrait {
	
	abstract public function badLogin();
	
	abstract public function bad2FACode():void;

	abstract protected function emailValidationDuration():\DateInterval;

	abstract protected function accountRecoveryDuration():\DateInterval;

	/**
	 * To override
	 * Return the base route for this Auth controller
	 * @return string
	 */
	public function _getBaseRoute(){
		return ClassUtils::getClassSimpleName(\get_class($this));
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
	protected function onBad2FACode():void{
		$this->bad2FACode();
	}
	
	/**
	 * To override
	 * Send the 2FA code to the user (email, sms, phone call...)
	 * @param string $code
	 * @param mixed $connected
	 */
	protected function _send2FACode(string $code,$connected):void{
		
	}
	
	/**
	 * To override
	 * Returns true if the creation of $accountName is possible.
	 * @param string $accountName
	 * @return bool
	 */
	protected function _newAccountCreationRule(string $accountName):?bool{
		
	}
	
	/**
	 * To override for defining user session key, default : "activeUser"
	 * @return string
	 */
	public function _getUserSessionKey():string {
		return 'activeUser';
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
	 * Sends an email for email checking.
	 * @param string $email
	 * @param string $validationURL
	 * @param string $expire
	 */
	protected function _sendEmailValidation(string $email,string $validationURL,string $expire):void{
		
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
	
	/**
	 * To override
	 * Returns the email from an account object.
	 * @param mixed $account
	 * @return string
	 */
	protected function getEmailFromNewAccount($account):string{
		return $account;
	}

	/**
	 * To override
	 * Returns the AuthTokens instance used for tokens generation when sending an email for the account creation.
	 * @return AuthTokens
	 */
	protected function getAuthTokensEmailValidation():AuthTokens{
		return new AuthTokens(self::$TOKENS_VALIDATE_EMAIL,10,$this->emailValidationDuration()->s,false);
	}

	/**
	 * To override
	 * Returns the AuthTokens instance used for tokens generation for a recovery account.
	 * @return AuthTokens
	 */
	protected function getAuthTokensAccountRecovery():AuthTokens{
		return new AuthTokens(self::$TOKENS_RECOVERY_ACCOUNT,10,$this->accountRecoveryDuration()->s,true);
	}

	/**
	 * To override
	 * Checks if a valid account matches this email.
	 * @param string $email
	 * @return bool
	 */
	protected function isValidEmailForRecovery(string $email):bool {
		return true;
	}

	/**
	 * Sends an email for account recovery (password reset).
	 * @param string $email
	 * @param string $validationURL
	 * @param string $expire
	 */
	protected function _sendEmailAccountRecovery(string $email,string $validationURL,string $expire):void{

	}

	/**
	 * To override
	 * Modifies the active password associated with the account corresponding to this email.
	 * @param string $email
	 * @param string $newPasswordHash
	 * @return bool
	 */
	protected function passwordResetAction(string $email,string $newPasswordHash):bool{
		return false;
	}

	protected function getAccountRecoveryLink():string{
		$href=$this->_getBaseRoute().'/recoveryInit';
		$target=$this->_getBodySelector();
		return "<a href='$href' data-target='$target'>Forgot your password?</a>";
	}
}

