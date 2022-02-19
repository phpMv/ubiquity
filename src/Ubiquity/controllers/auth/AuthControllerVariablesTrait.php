<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\utils\flash\FlashMessage;

/**
 * 
 * Ubiquity\controllers\auth$AuthControllerVariablesTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
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
	 * Displayed when email is valid.
	 * @param FlashMessage $fMessage
	 */
	protected function emailValidationSuccess(FlashMessage $fMessage){
		
	}
	
	/**
	 * To override
	 * Displayed when email is invalid or if an error occurs.
	 * @param FlashMessage $fMessage
	 */
	protected function emailValidationError(FlashMessage $fMessage){
		
	}

	/**
	 * To override
	 * Displayed when an account recovery operation is initiated.
	 * @param FlashMessage $fMessage
	 */
	protected function recoveryInitMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when email is sent for a recovery account operation.
	 * @param FlashMessage $fMessage
	 */
	protected function recoveryEmailSendMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when email is not associated with an existing account.
	 * @param FlashMessage $fMessage
	 */
	protected function recoveryEmailErrorMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when a new password is set with recovery account.
	 * @param FlashMessage $fMessage
	 */
	protected function resetPasswordSuccessMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when an error occurs when a new password is set with recovery account.
	 * @param FlashMessage $fMessage
	 */
	protected function resetPasswordErrorMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when the account recovery link is valid.
	 * @param FlashMessage $fMessage
	 */
	protected function emailAccountRecoverySuccess(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when the account recovery link is not valid.
	 * @param FlashMessage $fMessage
	 */
	protected function emailAccountRecoveryError(FlashMessage $fMessage){

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
	 * Override to define if user info is displayed as string.
	 * If set to true, use {{ _infoUser| raw }} in views to display user info.
	 * Remember to use $this->jquery->renderView instead of $this->loadView for the javascript generation.
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
	 * Returns the recovery account link caption.
	 * Default : Forgot your password?
	 * @return string
	 */
	protected function recoveryAccountCaption():string{
		return 'Forgot your password?';
	}

	/**
	 * Returns the body selector (jquery selector used for replacing the content of the page).
	 * default: main .container
	 *
	 * @return string
	 */
	public function _getBodySelector():string {
		return 'main .container';
	}

	protected function rememberCaption():string {
		return 'Remember me';
	}
	/**
	 * Returns true for account creation.
	 * @return boolean
	 */
	protected function hasAccountCreation():bool{
		return false;
	}
	
	/**
	 * 
	 * @return bool
	 */
	protected function hasEmailValidation():bool{
		return false;
	}

	/**
	 * @return bool
	 */
	protected function hasAccountRecovery():bool{
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
	 * To override
	 * Returns the default size for generated tokens.
	 * @return int
	 */
	protected function getTokenSize():int{
		return 6;
	}

	/**
	 * Generates a new random 2FA code.
	 * You have to override this basic implementation.
	 * @return string
	 * @throws \Exception
	 */
	protected function generate2FACode():string{
		return \bin2hex ( \random_bytes ($this->getTokenSize()));
	}

	/**
	 * Returns the code prefix (which should not be entered by the user). 
	 * @return string
	 */
	protected function towFACodePrefix():string{
		return 'U-';
	}

	/**
	 * Returns the default validity duration of a mail validation link.
	 * @return \DateInterval
	 */
	protected function emailValidationDuration():\DateInterval{
		return new \DateInterval('PT24H');
	}

	/**
	 * Returns the default validity duration of a generated 2FA code.
	 * @return \DateInterval
	 */
	protected function twoFACodeDuration():\DateInterval{
		return new \DateInterval('PT5M');
	}

	/**
	 * @return bool
	 */
	public function hasRecoveryAccount():bool{
		return false;
	}
	/**
	 * Returns the default validity duration for an email account recovery.
	 * @return \DateInterval
	 */
	protected function accountRecoveryDuration():\DateInterval{
		return new \DateInterval('PT30M');
	}
	
}

