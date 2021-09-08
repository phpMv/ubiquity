<?php
class ApacheWorkerCest {

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGotoFortunes(AcceptanceTester $I) {
		$I->amOnPage ( "/fortunes" );
		$I->seeElement ( 'body' );
		//$I->see ( 'Fortune: No such file or directory', [ 'css' => 'body' ] );
		//$I->see ( "A computer scientist is someone who fixes things that aren't broken.", [ 'css' => 'body' ] );
		//$I->see ( '<script>alert("This should not be displayed in a browser alert box.");</script>', [ 'css' => 'body' ] );
		//$I->see ( 'Feature: A bug with seniority.', [ 'css' => 'body' ] );
	}

	// tests
	public function tryToGotoDb(AcceptanceTester $I) {
		$I->amOnPage ( "/db" );
		$I->see ( 'id' );
		$I->see ( 'randomNumber' );
	}

	// tests
	public function tryToGotoQuery(AcceptanceTester $I) {
		$I->amOnPage ( "/db/query/2" );
		$I->see ( 'id' );
		$I->see ( 'randomNumber' );
	}

	// tests
	public function tryToGotoUpdate(AcceptanceTester $I) {
		$I->amOnPage ( "/db/update/2" );
		$I->see ( 'id' );
		$I->see ( 'randomNumber' );
	}
}
