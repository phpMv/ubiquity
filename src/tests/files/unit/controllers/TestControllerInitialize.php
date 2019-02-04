<?php

namespace controllers;

class TestControllerInitialize extends TestController {

	public function initialize() {
		parent::initialize ();
		echo 'initialize!-';
	}

	public function finalize() {
		parent::finalize ();
		echo '-finalize!';
	}
}

