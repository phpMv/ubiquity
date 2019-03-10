<?php
class JsonAPICest extends BaseAcceptance {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGet(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/user/1" );
		$I->see ( 'Benjamin' );
		$I->amOnPage ( "/jsonapi/user/" );
		$I->see ( 'Benjamin' );
	}

	// tests
	public function tryToGetLinks(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/links" );
		$I->see ( 'links' );
	}

	// tests
	public function tryToGetManyToOne(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/user/1/relationships/organization/" );
		$I->see ( 'Université de Caen-Normandie' );
	}

	// tests
	public function tryToGetManyToMany(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/user/1/relationships/groupes/" );
		$I->see ( 'Auditeurs' );
	}
}
