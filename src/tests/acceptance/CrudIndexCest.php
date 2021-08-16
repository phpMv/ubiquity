<?php
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

		$I->click ( "a[href=crud/connection/?]");
		$I->waitForText ( "Add a new models\Connection...", self::TIMEOUT, "body" );

	}

}
