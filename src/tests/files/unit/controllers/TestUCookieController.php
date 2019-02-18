<?php

namespace controllers;

use Ubiquity\utils\http\UCookie;

class TestUCookieController extends ControllerBase {

	public function index() {
		UCookie::set ( "user", "test-user" );
		UCookie::set ( "other", "pwd" );
		echo "cookie";
	}

	public function delete() {
		UCookie::delete ( "user" );
		echo "deleted";
	}

	public function get() {
		echo UCookie::get ( "user", "not-exists" );
	}

	public function deleteAll() {
		UCookie::deleteAll ();
		echo "deleteds";
	}
}

