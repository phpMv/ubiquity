<?php
class SigninCest {

	public function _before(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/blank.html" );
		 * $I->setCookie ( 'PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3' );
		 */
	}

	// tests
	public function tryToTest(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin" );
		$I->seeElement ( 'body' );
	}
}
