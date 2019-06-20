<?php

namespace Ubiquity\controllers\admin;

use Ubiquity\controllers\ControllerBase;
use controllers\Admin;
use Ubiquity\controllers\admin\popo\MaintenanceMode;

/**
 * Base class for maintenance controllers
 * Ubiquity\controllers\admin$DefaultMaintenance
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class DefaultMaintenance extends ControllerBase {
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
		$dimmer->asIcon ( "recycle", "Maintenance mode", "Our application is currently undergoing shedeled maintenance.<br>Thank you for your understanding." )->asPage ();
		$dimmer->setClosable ( false )->setBlurring ();
		$this->jquery->execAtLast ( '$("#maintenance").dimmer("show");' );
		echo $this->jquery->renderView ( '@framework/Admin/maintenance/default.html' );
	}
}

