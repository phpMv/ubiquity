<?php

namespace controllers;

use Ubiquity\utils\http\UCookie;

class TestUCookieController extends ControllerBase {

	public function index() {
		UCookie::set ( "user", "test-user" );
		UCookie::set ( "other", "pwd" );
		echo "cookie";
	}

	public function testUser() {
		echo UCookie::get ( "user", "not-exists" );
	}

	public function testOther() {
		echo UCookie::get ( "other", "not-exists" );
	}

	public function testNotExists() {
		echo UCookie::get ( "not-exists", "not-exists" );
	}

	public function delete() {
		UCookie::delete ( "user" );
		echo "deleted";
	}

	public function deleteAll() {
		UCookie::deleteAll ();
		echo "deleteds";
	}

	public function testAllDeleted() {
		echo UCookie::get ( "user", "no" ) . ':' . UCookie::get ( "other", "no" );
	}

	public function testDeleted() {
		echo UCookie::get ( "user", "no-user" );
	}
}

