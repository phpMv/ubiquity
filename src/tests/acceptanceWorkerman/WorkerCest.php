<?php
class WorkerCest {

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGotoFortunes(AcceptanceTester $I) {
		$I->amOnPage ( "/fortunes" );
		$I->seeElement ( 'body' );
		$I->see ( 'fortune: No such file or directory', [ 'css' => 'body' ] );
		$I->see ( "fA computer scientist is someone who fixes things that aren''t broken.", [ 'css' => 'body' ] );
		$I->see ( '<script>alert("This should not be displayed in a browser alert box.");</script>', [ 'css' => 'body' ] );
		$I->see ( 'Feature: A bug with seniority.', [ 'css' => 'body' ] );
	}
}
