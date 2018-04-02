<?php

namespace Ubiquity\controllers;

use Ubiquity\orm\DAO;

abstract class CRUDController extends ControllerBase {
	protected $model;
	protected $gui;

	abstract protected function getGui();

	/**
	 * Default page : list all objects
	 */
	public function index() {
		$objects = DAO::getAll ( $this->model );
		return $this->gui->renderObjects ( $objects );
	}
}

