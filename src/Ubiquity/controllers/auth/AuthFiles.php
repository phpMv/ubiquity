<?php

namespace Ubiquity\controllers\auth;

/**
 * 
 * Ubiquity\controllers\auth$AuthFiles
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
class AuthFiles {
	protected string $viewBase;

	public function __construct() {
		$this->viewBase = '@framework/auth';
	}

	/**
	 * To override
	 * The login view.
	 *
	 * @return string
	 */
	public function getViewIndex(): string {
		return $this->viewBase . '/index.html';
	}

	/**
	 * To override
	 * The info view, displays the connected user and a logout button.
	 *
	 * @return string
	 */
	public function getViewInfo(): string {
		return $this->viewBase . '/info.html';
	}

	/**
	 * To override
	 * Displays the form for a new account.
	 *
	 * @return string
	 */
	public function getViewCreate(): string {
		return $this->viewBase . '/create.html';
	}
	
	/**
	 * To override
	 * Displays the form for step two.
	 *
	 * @return string
	 */
	public function getViewStepTwo(): string {
		return $this->viewBase . '/stepTwo.html';
	}
	
	/**
	 * To override
	 * Displays the message if the 2FA code is bad.
	 *
	 * @return string
	 */
	public function getViewBadTwoFACode(): string {
		return $this->viewBase . '/badTwoFACode.html';
	}
	
	/**
	 * To override
	 *
	 * @return string
	 */
	public function getViewNoAccess(): string {
		return $this->viewBase . '/noAccess.html';
	}

	/**
	 * Returns the base template for all Auth actions if getBaseTemplate return a base template filename.
	 *
	 * @return string
	 */
	public function getViewBaseTemplate(): string {
		return $this->viewBase . '/baseTemplate.html';
	}

	/**
	 * To override
	 *
	 * @return string
	 */
	public function getViewDisconnected(): string {
		return $this->viewBase . '/disconnected.html';
	}

	/**
	 *
	 * @return string
	 */
	public function getViewMessage(): string {
		return $this->viewBase . '/message.html';
	}

	/**
	 * To override
	 * @return string
	 */
	public function getViewInitRecovery(): string {
		return $this->viewBase . '/initRecovery.html';
	}

	/**
	 * To override
	 * @return string
	 */
	public function getViewRecovery(): string {
		return $this->viewBase . '/recovery.html';
	}

	/**
	 * To override
	 * Returns the base template filename, default : null
	 * @return  string|null
	 */
	public function getBaseTemplate() {
		return;
	}
}

