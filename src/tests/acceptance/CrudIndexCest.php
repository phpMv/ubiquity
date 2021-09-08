<?php
include_once 'tests/acceptance/BaseAcceptance.php';
class CrudIndexCest extends BaseAcceptance {

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGotoCrudIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/crud/home" );
		$I->see ( "User" );
		$I->see ( "Groupe" );
		$I->see ( "Organization" );
		$I->see ( "Settings" );
		$I->see ( "Organizationsettings" );

		$I->click ( '//a[@href="crud/connection/?"]');
		$I->waitForText ( "Add a new models\Connection...", self::TIMEOUT, "body" );

	}
	
	// tests
	public function tryToSeeRelations(AcceptanceTester $I) {
		$I->amOnPage ( "/crud/home" );
		$I->see ( "Groupe" );

		$I->click ( '//a[@href="crud/groupe/?"]');
		$I->waitForText ( "Add a new models\Groupe...", self::TIMEOUT, "body" );
		
		$I->doubleClick( '//tr[@data-ajax="2"]');
		$I->waitForText ( "users (11)", self::TIMEOUT, "body" );
		
		$I->click('//div[@data-ajax="models.User||98"]');
		$I->waitForElement( '//a[@data-page="17"]', self::TIMEOUT);
		
	}

}
