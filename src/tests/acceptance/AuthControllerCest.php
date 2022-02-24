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

	// tests
	public function tryToConnectWithConfig(AcceptanceTester $I){
		$I->amOnPage ( "/TestMainControllerWithAuth" );
		$I->see ( "Forbidden access" );
		$I->see ( "You are not authorized to access the page TestMainControllerWithAuth !" );
		$I->click ( "a._login" );
		$I->waitForText ( "Se souvenir de moi", self::TIMEOUT, "body" );
		$I->canSee("","body");
		// Test bad creditentials
		$I->fillField ( "[name='email']", "jeremy.bryan" );
		$I->fillField ( "[name='password']", "0000" );
		$I->click ( "button._connect" );
		$I->waitForText ( "Connection problem", self::TIMEOUT, "body" );
		$I->canSee ( "Invalid creditentials!", "body" );
		// Test connection
		$I->amOnPage ( "/TestMainControllerWithAuth" );
		$I->see ( "Forbidden access" );
		$I->click ( "a._login" );
		$I->waitForText ( "Se souvenir de moi", self::TIMEOUT, "body" );
		$I->fillField ( "[name='email']", "myaddressmail@gmail.com" );
		$I->fillField ( "[name='password']", "0000" );
		$I->click ( "button._connect" );
		//2FA
		$I->waitForText('Two factor Authentification');
		$I->click ( "button._validate2FA" );
		$I->waitForText ( "Hello world!", self::TIMEOUT, "body" );
		// Test access to other page
		$I->amOnPage ( "/TestMainControllerWithAuth/test" );
		$I->see ( "test ok!" );
		// Test Logout
		$I->click ( "a._logout" );
		$I->waitForText ( "You have been properly disconnected!", self::TIMEOUT, "body" );
		//Bad 2FA
		$I->amOnPage ( "/TestMainControllerWithAuth" );
		$I->see ( "Forbidden access" );
		$I->click ( "a._login" );
		$I->fillField ( "[name='email']", "myaddressmail@gmail.com" );
		$I->fillField ( "[name='password']", "0000" );
		$I->click ( "button._connect" );
		$I->waitForText('Two factor Authentification');
		$I->see('code submited!');
		$I->fillField ( "[name='code']", "0000" );
		$I->click ( "button._validate2FA" );
		$I->waitForText('Invalid 2FA code!');
		//Re send 2FA
		$I->click ( "a._send" );
		$I->waitForText('A new code was submited.');
		// Test no access
		$I->amOnPage ( "/TestMainControllerWithAuth" );
		$I->see ( "Forbidden access" );
		$I->amOnPage ( "/TestMainControllerWithAuth/test" );
		$I->see ( "Forbidden access" );
		//Create account
		$I->amOnPage ( "/TestMainControllerWithAuth" );
		$I->click ( "a._login" );
		$I->waitForText("Don't have an account yet?",self::TIMEOUT,'body');
		$I->click ( "a._create" );
		$I->waitForText("Account creation",self::TIMEOUT,"body");
		$I->fillField ( "[name='email']", "jeremy.bryan@gmail.com" );
		$I->fillField ( "[name='password']", "0000" );
		$I->fillField ( "[name='password-conf']", "0000" );
		$I->click ( "button._create" );
		//Email confirmation
		$I->waitForText("Account creation",self::TIMEOUT,"body");
		$I->see ( "account created with success!" );
		$I->see('Confirm your email address');
		$I->see('jeremy.bryan@gmail.com');
		$I->click ( "#url" );
		$I->waitForText("Account creation",self::TIMEOUT,"body");
		$I->see("has been validated.");
		$I->see('jeremy.bryan@gmail.com');
		//Account recovery
		$I->amOnPage ( "/TestMainControllerWithAuth" );
		$I->click ( "a._login" );
		$I->waitForText("Don't have an account yet?",self::TIMEOUT,'body');
		$I->click ( "a._recovery" );
		$I->waitForText("Account recovery",self::TIMEOUT,"body");
		$I->see('Enter the email associated with your account to receive a password reset link.');
		$I->fillField ( "[name='email']", "recovery@gmail.com" );
		$I->click ( "button._recoverySend" );
		$I->waitForText("Account recovery",self::TIMEOUT,"body");
		$I->see('You can only use this link temporarily, from the same machine, on this browser.');
		$I->see('recovery@gmail.com');
		$I->click ( "#url" );
		$I->waitForText('Account recovery (password reset)');
		$I->fillField ( "[name='password']", "0000" );
		$I->fillField ( "[name='password-conf']", "0000" );
		$I->click ( "button._submit" );
		$I->waitForText('Your password has been updated correctly for the account associated with');
		$I->see('recovery@gmail.com');
	}
}
