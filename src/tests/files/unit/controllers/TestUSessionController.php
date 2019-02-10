<?php

namespace controllers;

use Ubiquity\utils\http\USession;

/**
 * Controller TestUSessionController
 */
class TestUSessionController extends ControllerBase {

	public function index() {
		USession::set ( "user", "test-user" );
		echo "session";
	}

	public function testUser() {
		if (USession::exists ( "user" ))
			echo USession::get ( "user" );
	}

	public function testTmp() {
		USession::setTmp ( "tmp", "tmpValue", 15 );
		echo "testTmp";
	}

	public function getTmp() {
		echo USession::getTmp ( "tmp" );
	}

	public function getTmpExpired() {
		echo USession::getTmp ( "tmp", "null" );
	}

	public function delete() {
		USession::delete ( "user" );
		echo "delete-user";
	}

	public function testDelete() {
		echo USession::get ( "user", "user-null" );
	}

	public function terminate() {
		USession::terminate ();
		if (sizeof ( $_SESSION ) == 0)
			echo "terminated";
	}
}
