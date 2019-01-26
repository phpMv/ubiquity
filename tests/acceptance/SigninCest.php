<?php
class SigninCest {

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToTest(AcceptanceTester $I) {
		$I->seeElement ( 'body' );
	}
}
