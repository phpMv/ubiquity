<?php

namespace tests\unit\controllers\controllers;

use Ubiquity\controllers\Controller;

class TestController extends Controller {

	public function index() {
		echo "Hello world!";
	}

	public function doForward() {
		echo "forward!";
	}
}

