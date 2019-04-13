<?php

namespace controllers;

use Ubiquity\controllers\Controller;

class TestController extends Controller {

	/**
	 *
	 * @route("/route/test/(index/)?")
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\Controller::index()
	 */
	public function index() {
		echo "Hello world!";
	}

	public function test() {
		echo "test!";
	}

	public function doForward() {
		echo "forward!";
	}
}

