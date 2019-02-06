<?php
class AuthControllerCest {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToConnect(AcceptanceTester $I) {
		$I->amOnPage ( "/TestControllerWithAuth" );
		$I->see ( "Forbidden access" );
		$I->see ( "You are not authorized to access the page TestControllerWithAuth !" );
		$I->click ( "a._login" );
		$I->waitForText ( "Remember me", self::TIMEOUT, "body" );
		// Test bad creditentials
		$I->fillField ( "[name='email']", "jeremy.bryan" );
		$I->fillField ( "[name='password']", "0000" );
		$I->click ( "button._connect" );
		$I->waitForText ( "Connection problem", self::TIMEOUT, "body" );
		$I->canSee ( "Invalid creditentials!", "body" );
		// Test connection
		$I->amOnPage ( "/TestControllerWithAuth" );
		$I->see ( "Forbidden access" );
		$I->click ( "a._login" );
		$I->waitForText ( "Remember me", self::TIMEOUT, "body" );
		$I->fillField ( "[name='email']", "jeremy.bryan" );
		$I->fillField ( "[name='password']", "TTV64OAQ9AN" );
		$I->click ( "button._connect" );
		$I->waitForText ( "Welcome jeremy.bryan!", self::TIMEOUT, "body" );
		// Test access to other page
		$I->amOnPage ( "/TestControllerWithAuth/autre" );
		$I->see ( "autre!" );
		$I->see ( "jeremy.bryan" );
		// Test Logout
		$I->click ( "a._logout" );
		$I->waitForText ( "You have been properly disconnected!", self::TIMEOUT, "body" );
		// Test no access
		$I->amOnPage ( "/TestControllerWithAuth" );
		$I->see ( "Forbidden access" );
		$I->amOnPage ( "/TestControllerWithAuth/autre" );
		$I->see ( "Forbidden access" );
	}
}
