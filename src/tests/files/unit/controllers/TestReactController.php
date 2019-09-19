<?php

namespace controllers;

use Ubiquity\controllers\Controller;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;

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

	/**
	 *
	 * @route("/react/set/session")
	 */
	public function testSetSession() {
		USession::set ( 'user', 'me' );
		echo 'me';
	}

	/**
	 *
	 * @route("/react/get/session")
	 */
	public function testGetSession() {
		$v = USession::get ( 'user', 'not me' );
		echo $v;
	}
}

