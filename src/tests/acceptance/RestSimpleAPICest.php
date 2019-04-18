<?php
class RestSimpleAPICest extends BaseAcceptance {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGet(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/1" );
		$I->see ( 'lecnam.net' );
		$I->see ( 'Personnels' );
		$I->see ( 'Auditeurs' );
		$I->see ( 'Wyatt' );
		$I->see ( 'Maris' );

		$I->amOnPage ( "/jsonapi/user/" );
		$I->see ( 'Benjamin' );
	}

	// tests
	public function tryToGetWithInclude(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/1?include=organizationsettingss" );
		$I->see ( 'lecnam.net' );
		$I->amOnPage ( "/rest/simple/orgas/1?include=groupes" );
		$I->see ( 'lecnam.net' );
		$I->see ( 'Personnels' );
		$I->see ( 'Auditeurs' );
	}

	// tests
	public function tryToGetMultiple(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/" );
		$I->see ( '"count": 6' );
	}
}
