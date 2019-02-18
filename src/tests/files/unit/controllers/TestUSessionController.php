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

	public function testInc() {
		USession::set ( "inc", 10 );
		USession::inc ( "inc" );
		echo USession::session ( "inc" );
	}

	public function testDec() {
		USession::set ( "dec", 10 );
		USession::dec ( "dec", 2 );
		echo USession::session ( "dec" );
	}

	public function testApply() {
		USession::set ( "apply", "MAJUSCULE" );
		USession::apply ( "apply", "strtolower" );
		echo USession::session ( "apply" );
	}

	public function testApplyBis() {
		USession::set ( "apply2", "content" );
		USession::apply ( "apply2", function ($v) {
			return 'prefix-' . $v;
		} );
		echo USession::session ( "apply2" );
	}

	public function terminate() {
		USession::terminate ();
		if (sizeof ( $_SESSION ) == 0)
			echo "terminated";
	}
}
