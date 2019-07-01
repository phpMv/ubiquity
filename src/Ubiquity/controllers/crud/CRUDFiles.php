<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\controllers\crud\traits\UrlsTrait;

/**
 *
 * @author jc
 *
 */
class CRUDFiles {
	use UrlsTrait;
	protected $viewBase;

	/**
	 * To override for defining viewBase (default : @framework/crud)
	 */
	public function __construct() {
		$this->viewBase = "@framework/crud";
	}

	/**
	 * To override
	 * Returns the template for the index route (default : @framework/crud/index.html)
	 *
	 * @return string
	 */
	public function getViewIndex() {
		return $this->viewBase . "/index.html";
	}

	/**
	 * To override
	 * Returns the template for the edit and new instance routes (default : @framework/crud/form.html)
	 *
	 * @return string
	 */
	public function getViewForm() {
		return $this->viewBase . "/form.html";
	}

	/**
	 * To override
	 * Returns the template for the display route (default : @framework/crud/display.html)
	 *
	 * @return string
	 */
	public function getViewDisplay() {
		return $this->viewBase . "/display.html";
	}

	/**
	 * Returns the base template for all Crud actions if getBaseTemplate return a base template filename
	 *
	 * @return string
	 */
	public function getViewBaseTemplate() {
		return $this->viewBase . "/baseTemplate.html";
	}

	/**
	 * To override
	 * Returns the base template filename, default : null
	 */
	public function getBaseTemplate() {
		return;
	}
}

