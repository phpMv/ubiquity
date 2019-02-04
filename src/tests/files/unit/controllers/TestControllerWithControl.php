<?php

namespace controllers;

use Ubiquity\utils\http\USession;

class TestControllerWithControl extends TestControllerInitialize {

	public function onInvalidControl() {
		parent::onInvalidControl ();
		echo "invalid!";
	}

	public function validAction() {
		echo "valid action!";
	}

	/**
	 *
	 * @route("/route/test/ctrl/")
	 */
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

	/**
	 *
	 * @route("/route/test/params/{a}/{b}")
	 * @param string $a
	 * @param string $b
	 */
	public function withParams($a, $b = "default") {
		echo $a . "-" . $b . "!";
	}
}

