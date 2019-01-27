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
		$I->amOnPage ( "/" );
		$I->seeElement ( 'body' );
	}

	// tests
	public function tryToGotoAdmin(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin/index" );
		$I->seeInCurrentUrl ( "Admin/index" );
		$I->see ( 'Used to perform CRUD operations on data.', '.description' );
	}
}
