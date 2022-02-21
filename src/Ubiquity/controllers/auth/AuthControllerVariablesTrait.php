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
	 * To override
	 * Returns int the maximum number of allowed login attempts.
	 */
	protected function attemptsNumber() {
		return;
	}

	/**
	 * To override
	 * Returns the time before trying to connect again
	 * Effective only if attemptsNumber return a number.
	 *
	 * @return int
	 */
	protected function attemptsTimeout():int {
		return 3 * 60;
	}

	/**
	 * Override to define if user info is displayed as string.
	 * If set to true, use {{ _infoUser| raw }} in views to display user info.
	 * Remember to use $this->jquery->renderView instead of $this->loadView for the javascript generation.
	 */
	public function _displayInfoAsString(): bool {
		return false;
	}

	/**
	 * To override for defining user session key, default : "activeUser"
	 * @return string
	 */
	public function _getUserSessionKey():string {
		return 'activeUser';
	}

	public function _checkConnectionTimeout() {
		return;
	}

	public function _getLoginInputName(): string {
		return 'email';
	}

	protected function loginLabel():string {
		return \ucfirst ( $this->_getLoginInputName () );
	}

	public function _getPasswordInputName():string {
		return 'password';
	}

	protected function passwordLabel(): string {
		return \ucfirst ( $this->_getPasswordInputName () );
	}
	
	protected function passwordConfLabel(): string {
		return \ucfirst ( $this->_getPasswordInputName () ).' confirmation';
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

}

