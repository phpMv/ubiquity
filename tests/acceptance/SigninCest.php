<?php
class SigninCest {

	public function _before(AcceptanceTester $I) {
		$I->amOnPage ( "/" );
	}

	// tests
	public function tryToTest(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin" );
		$I->seeElement ( 'body' );
	}
}
