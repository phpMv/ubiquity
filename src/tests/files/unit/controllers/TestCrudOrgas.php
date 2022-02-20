<?php

namespace controllers;

use controllers\crud\datas\TestCrudOrgasDatas;
use Ubiquity\controllers\crud\CRUDDatas;
use controllers\crud\viewers\TestCrudOrgasViewer;
use Ubiquity\controllers\crud\viewers\ModelViewer;
use controllers\crud\events\TestCrudOrgasEvents;
use Ubiquity\controllers\crud\CRUDEvents;
use controllers\crud\files\TestCrudOrgasFiles;
use Ubiquity\controllers\crud\CRUDFiles;

/**
 * CRUD Controller TestCrudOrgas
 */
class TestCrudOrgas extends \Ubiquity\controllers\crud\CRUDController {

	public function __construct() {
		parent::__construct ();
		$this->model = "models\\Organization";
	}

	public function _getBaseRoute(): string {
		return 'TestCrudOrgas';
	}

	protected function getAdminData(): CRUDDatas {
		return new TestCrudOrgasDatas ( $this );
	}

	protected function getModelViewer(): ModelViewer {
		return new TestCrudOrgasViewer ( $this );
	}

	protected function getEvents(): CRUDEvents {
		return new TestCrudOrgasEvents ( $this );
	}

	protected function getFiles(): CRUDFiles {
		return new TestCrudOrgasFiles ();
	}
}
