<?php
class JsonAPICest extends BaseAcceptance {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGet(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/user/1" );
		$I->see ( 'Benjamin' );
		$I->amOnPage ( "/jsonapi/user/" );
		$I->see ( 'Benjamin' );
	}
}
