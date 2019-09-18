<?php
class IndexCest {

	public function _before(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/blank.html" );
		 * $I->setCookie ( 'PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3' );
		 */
	}

	// tests
	public function tryToGotoIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/" );
		$I->seeElement ( 'body' );
		$I->see ( 'Ubiquity', [ 'css' => 'body' ] );
	}

	// tests
	public function tryToGotoAdminIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin/index" );
		$I->seeInCurrentUrl ( "Admin/index" );
		$I->see ( 'Used to perform CRUD operations on data', [ 'css' => 'body' ] );
	}
}
