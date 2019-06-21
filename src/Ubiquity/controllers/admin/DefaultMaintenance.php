<?php

namespace Ubiquity\controllers\admin;

use Ubiquity\controllers\ControllerBase;
use controllers\Admin;
use Ubiquity\controllers\admin\popo\MaintenanceMode;

abstract class DefaultMaintenance extends ControllerBase {
	protected $activeMaintenance;

	public function __construct() {
		parent::__construct ();
		$config = Admin::getConfigFile () ['maintenance'];
		$this->activeMaintenance = MaintenanceMode::getActiveMaintenance ( $config );
	}

	public function isValid($action) {
		return isset ( $this->activeMaintenance );
	}

	public function onInvalidControl() {
		parent::onInvalidControl ();
		echo "No active maintenance!";
		exit ();
	}

	public function index() {
		$dimmer = $this->jquery->semantic ()->htmlDimmer ( 'maintenance' );
		$dimmer->asIcon ( $this->activeMaintenance->getIcon (), $this->activeMaintenance->getTitle (), $this->activeMaintenance->getMessage () )->asPage ();
		$dimmer->setClosable ( false )->setBlurring ();
		$this->jquery->execAtLast ( '$("#maintenance").dimmer("show");' );
		echo $this->jquery->renderView ( '@framework/Admin/maintenance/default.html' );
	}
}

