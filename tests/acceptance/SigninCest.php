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
		$I->see ( 'Ubiquity', [ 'css' => 'body' ] );
	}

	// tests
	public function tryToGotoAdmin(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin/index" );
		$I->seeInCurrentUrl ( "Admin/index" );
		$I->see ( 'Used to perform CRUD operations on data', [ 'css' => 'body' ] );
		$I->click ( "a[href='Admin/Models']" );
		$I->waitForElementVisible ( "#content-header", 10 );
		$I->canSeeInCurrentUrl ( "/Admin/Models" );
		$I->click ( "a[data-model='models.Connection']" );
		$I->waitForElementVisible ( "#btAddNew", 10 );
		$I->canSeeInCurrentUrl ( "/Admin/showModel/models.Connection" );
		$I->see ( 'organizations/display/4', "#lv td" );
	}
}
