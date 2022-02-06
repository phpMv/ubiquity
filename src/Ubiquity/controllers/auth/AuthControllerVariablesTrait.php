<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\utils\flash\FlashMessage;

trait AuthControllerVariablesTrait {

	/**
	 * Override for modifying the noAccess message
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function noAccessMessage(FlashMessage $fMessage) {
	}

	/**
	 * Override for modifying attempts message
	 * You can use {_timer} and {_attemptsCount} variables in message content
	 *
	 * @param FlashMessage $fMessage
	 * @param int $attempsCount
	 */
	protected function attemptsNumberMessage(FlashMessage $fMessage, $attempsCount) {
	}

	/**
	 * To override for modifying the bad login message
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function badLoginMessage(FlashMessage $fMessage) {
	}

	/**
	 * To override for modifying the logout message
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function terminateMessage(FlashMessage $fMessage) {
	}

	/**
	 * To override for modifying the disconnect message
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function disconnectedMessage(FlashMessage $fMessage) {
	}
	
	/**
	 * To override for modifying the account creation message.
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function createAccountMessage(FlashMessage $fMessage) {
	}
	
	/**
	 * To override for modifying the account creation message information.
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function canCreateAccountMessage(FlashMessage $fMessage) {
	}
	
	/**
	 * To override for modifying the error for account creation.
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function createAccountErrorMessage(FlashMessage $fMessage) {
	}
	
	/**
	 * To override for modifying the 2FA panel message.
	 * @param FlashMessage $fMessage
	 */
	protected function twoFAMessage(FlashMessage $fMessage){
		
	}
	/**
	 * To override
	 * @param FlashMessage $fMessage
	 */
	protected function newTwoFACodeMessage(FlashMessage $fMessage){
		
	}
	
	/**
	 * To override for modifying the message displayed if the 2FA code is bad.
	 * @param FlashMessage $fMessage
	 */
	protected function twoFABadCodeMessage(FlashMessage $fMessage){
		
	}

	/**
	 * To override
	 * Returns the maximum number of allowed login attempts.
	 */
	protected function attemptsNumber() {
		return;
	}

	/**
	 * To override
	 * Returns the time before trying to connect again
	 * Effective only if attemptsNumber return a number.
	 *
	 * @return number
	 */
	protected function attemptsTimeout() {
		return 3 * 60;
	}

	/**
	 * Override to define if info is displayed as string
	 * if set to true, use _infoUser var in views to display user info
	 */
	public function _displayInfoAsString() {
		return false;
	}

	public function _checkConnectionTimeout() {
		return;
	}

	public function _getLoginInputName() {
		return 'email';
	}

	protected function loginLabel() {
		return \ucfirst ( $this->_getLoginInputName () );
	}

	public function _getPasswordInputName() {
		return 'password';
	}

	protected function passwordLabel() {
		return \ucfirst ( $this->_getPasswordInputName () );
	}
	
	protected function passwordConfLabel() {
		return \ucfirst ( $this->_getPasswordInputName () ).' confirmation';
	}

	/**
	 * Returns the body selector (jquery selector used for replacing the content of the page).
	 * default: body
	 *
	 * @return string
	 */
	public function _getBodySelector() {
		return 'body';
	}

	protected function rememberCaption() {
		return 'Remember me';
	}
	/**
	 * Returns true for account creation.
	 * @return boolean
	 */
	protected function hasAccountCreation(){
		return false;
	}
	
	/**
	 * To override
	 * Returns true for a two factor authentification for this account.
	 * @param mixed $accountValue
	 * @return bool
	 */
	protected function has2FA($accountValue=null):bool{
		return false;
	}
	
	/**
	 * 
	 * @return string
	 */
	protected function generate2FACode():string{
		return \substr(\md5(\uniqid(\rand(), true)), 4, 4);
	}
	
	protected function towFACodePrefix():string{
		return 'U-';
	}
}

