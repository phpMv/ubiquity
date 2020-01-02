<?php

namespace Ubiquity\controllers\auth;

class AuthFiles {
	protected $viewBase;

	public function __construct() {
		$this->viewBase = "@framework/auth";
	}

	/**
	 * To override
	 * The login view
	 *
	 * @return string
	 */
	public function getViewIndex() {
		return $this->viewBase . "/index.html";
	}

	/**
	 * To override
	 * The info view, displays the connected user and a logout button
	 *
	 * @return string
	 */
	public function getViewInfo() {
		return $this->viewBase . "/info.html";
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
	 * Returns the base template for all Auth actions if getBaseTemplate return a base template filename
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

