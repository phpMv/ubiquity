<?php

namespace tests\unit\controllers\controllers;

use Ubiquity\utils\http\USession;

class TestControllerWithControl extends TestControllerInitialize {

	public function onInvalidControl() {
		parent::onInvalidControl ();
		echo "invalid!";
	}

	public function validAction() {
		echo "valid action!";
	}

	public function actionWithControl() {
		echo "authorized!";
	}

	public function isValid($action) {
		parent::isValid ( $action );
		if ($action == 'actionWithControl') {
			return USession::exists ( 'user' );
		}
		return true;
	}

	public function withParams($a, $b = "default") {
		echo $a . "-" . $b . "!";
	}
}

