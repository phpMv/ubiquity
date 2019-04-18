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
		$I->see ( 'Personnels' );
		$I->see ( 'Auditeurs' );
		$I->see ( 'Etudiants' );
		$I->see ( 'Enseignants' );
		$I->see ( 'Vacataires' );
		$I->see ( '"count":6' );
	}

	// tests
	public function tryToGetFilter(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/?filter=name='CONSERVATOIRE NATIONAL DES ARTS ET MéTIERS'" );
		$I->see ( 'lecnam.net' );
		$I->amOnPage ( "/rest/simple/orgas/?filter=name like 'C*'" );
		$I->see ( 'lecnam.net' );
	}

	// tests
	public function tryToGetPaginate(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/?page[number]=1" );
		$I->see ( 'lecnam.net' );
		$I->amOnPage ( "/rest/simple/orgas/?page[number]=2" );
		$I->see ( 'unicaen.fr' );
		$I->see ( '"count":1' );
		$I->amOnPage ( "/rest/simple/orgas/?page[number]=1&page[size]=2" );
		$I->see ( 'lecnam.net' );
		$I->see ( 'unicaen.fr' );
		$I->see ( '"count":2' );
	}
}
