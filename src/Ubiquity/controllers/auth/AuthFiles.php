<?php

namespace Ubiquity\controllers\auth;

class AuthFiles {
	protected $viewBase;

	public function __construct() {
		$this->viewBase = "@framework/auth";
	}

	/**
	 * To override
	 * The login view.
	 *
	 * @return string
	 */
	public function getViewIndex() {
		return $this->viewBase . "/index.html";
	}

	/**
	 * To override
	 * The info view, displays the connected user and a logout button.
	 *
	 * @return string
	 */
	public function getViewInfo() {
		return $this->viewBase . "/info.html";
	}

	/**
	 * To override
	 * Displays the form for a new account.
	 *
	 * @return string
	 */
	public function getViewCreate() {
		return $this->viewBase . "/create.html";
	}
	
	/**
	 * To override
	 * Displays the form for step two.
	 *
	 * @return string
	 */
	public function getViewStepTwo() {
		return $this->viewBase . "/stepTwo.html";
	}
	
	/**
	 * To override
	 * Displays the message if the 2FA code is bad.
	 *
	 * @return string
	 */
	public function getViewBadTwoFACode() {
		return $this->viewBase . "/badTwoFACode.html";
	}
	
	/**
	 * To override
	 *
	 * @return string
	 */
	public function getViewNoAccess() {
		return $this->viewBase . "/noAccess.html";
	}

	/**
	 * Returns the base template for all Auth actions if getBaseTemplate return a base template filename.
	 *
	 * @return string
	 */
	public function getViewBaseTemplate() {
		return $this->viewBase . "/baseTemplate.html";
	}

	/**
	 * To override
	 *
	 * @return string
	 */
	public function getViewDisconnected() {
		return $this->viewBase . "/disconnected.html";
	}

	/**
	 *
	 * @return string
	 */
	public function getViewMessage() {
		return $this->viewBase . "/message.html";
	}

	/**
	 * To override
	 * Returns the base template filename, default : null
	 */
	public function getBaseTemplate() {
		return;
	}
}

