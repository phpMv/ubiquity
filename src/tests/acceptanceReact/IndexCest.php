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
	public function tryToGotoReactIndex(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/react/test/index" );
		 * $I->seeInCurrentUrl ( "/react/test/index" );
		 * $I->see ( 'Hello react!', [ 'css' => 'body' ] );
		 */
	}

	// tests
	public function tryToGotoReactGet(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/react/get" );
		 * $I->see ( '500', [ 'css' => 'body' ] );
		 * $I->amOnPage ( "/react/get?p=555" );
		 * $I->see ( '555', [ 'css' => 'body' ] );
		 */
	}

	// tests
	public function tryToGotoReactGetSetSession(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/react/set/session" );
		 * $I->see ( 'me', [ 'css' => 'body' ] );
		 * $I->amOnPage ( "/react/get/session" );
		 * $I->see ( 'me', [ 'css' => 'body' ] );
		 */
	}
}
