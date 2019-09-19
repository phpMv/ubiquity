<?php

namespace controllers;

use Ubiquity\controllers\Controller;
use Ubiquity\utils\http\URequest;

class TestReactController extends Controller {

	/**
	 *
	 * @route("/react/test/(index/)?")
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\Controller::index()
	 */
	public function index() {
		echo "Hello react!";
	}

	/**
	 *
	 * @route("/react/get")
	 */
	public function testGet() {
		$p = URequest::get ( 'p', 500 );
		echo $p;
	}
}

