<?php
class SigninCest {

	public function _before(AcceptanceTester $I) {
		$I->setCookie ( 'PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3' );
		$I->amOnPage ( "/" );
	}

	// tests
	public function tryToTest(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin" );
		$I->seeElement ( 'body' );
	}
}
