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
	public function __construct(string $viewBase='@framework/crud') {
		$this->viewBase = $viewBase;
	}

	/**
	 * To override
	 * Returns the template for the index route (default : @framework/crud/index.html)
	 *
	 * @return string
	 */
	public function getViewIndex():string {
		return $this->viewBase . '/index.html';
	}
	
	/**
	 * To override with MultiResourceCRUDController only
	 * Returns the template for the home route (default : @framework/crud/home.html)
	 *
	 * @return string
	 */
	public function getViewHome(): string {
		return $this->viewBase . '/home.html';
	}

	/**
	 * To override with MultiResourceCRUDController only
	 * Returns the template for an item in home route (default : @framework/crud/itemHome.html)
	 *
	 * @return string
	 */
	public function getViewItemHome(): string {
		return $this->viewBase . '/itemHome.html';
	}

	/**
	 * To override with MultiResourceCRUDController only
	 * Returns the template for displaying models in a dropdown (default : @framework/crud/itemHome.html)
	 *
	 * @return string
	 */
	public function getViewNav(): string {
		return $this->viewBase . '/nav.html';
	}

	/**
	 * To override
	 * Returns the template for the edit and new instance routes (default : @framework/crud/form.html)
	 *
	 * @return string
	 */
	public function getViewForm(): string {
		return $this->viewBase . '/form.html';
	}

	/**
	 * To override
	 * Returns the template for the display route (default : @framework/crud/display.html)
	 *
	 * @return string
	 */
	public function getViewDisplay(): string {
		return $this->viewBase . '/display.html';
	}

	/**
	 * Returns the base template for all Crud actions if getBaseTemplate return a base template filename
	 *
	 * @return string
	 */
	public function getViewBaseTemplate(): string {
		return $this->viewBase . '/baseTemplate.html';
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

