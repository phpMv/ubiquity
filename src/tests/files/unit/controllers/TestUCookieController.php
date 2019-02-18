<?php

namespace controllers;

use Ubiquity\utils\http\UCookie;

class TestUCookieController extends ControllerBase {

	public function index() {
		UCookie::set ( "user", "test-user" );
		echo "cookie";
	}

	public function testUser() {
		if (UCookie::exists ( "user" ))
			echo UCookie::get ( "user" );
	}

	public function delete() {
		UCookie::delete ( "user" );
		echo "deleted";
	}

	public function testDeleted() {
		echo UCookie::get ( "user", "no-user" );
	}
}

