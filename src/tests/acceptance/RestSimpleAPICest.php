<?php
class RestSimpleAPICest extends BaseAcceptance {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGet(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/1" );
		$I->see ( 'lecnam.net' );
		$I->see ( 'cnam-basse-normandie.fr;' );
	}

	// tests
	public function tryToGetWithInclude(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/1/organizationsettingss" );
		$I->see ( 'lecnam.net' );
		$I->amOnPage ( "/rest/simple/orgas/1/groupes" );
		$I->see ( 'lecnam.net' );
		$I->see ( 'Personnels' );
		$I->see ( 'Auditeurs' );
	}

	// tests
	public function tryToGetMultiple(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/list/" );
		$I->see ( 'Conservatoire' );
		$I->see ( 'Campus' );
		$I->see ( 'lycee' );
	}

	// tests
	public function tryToGetFilter(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/list/name='CONSERVATOIRE NATIONAL DES ARTS ET MéTIERS'" );
		$I->see ( 'lecnam.net' );
		$I->amOnPage ( "/rest/simple/orgas/list/name like 'C*'" );
		$I->see ( 'lecnam.net' );
	}

	// tests
	public function tryToGetPaginate(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/list/?page[number]=1" );
		$I->see ( 'lecnam.net' );
		$I->amOnPage ( "/rest/simple/orgas/list/?page[number]=2" );
		$I->see ( 'unicaen.fr' );
		//$I->see ( '"count":1' );
		$I->amOnPage ( "/rest/simple/orgas/list/?page[number]=1&page[size]=2" );
		$I->see ( 'lecnam.net' );
		$I->see ( 'unicaen.fr' );
		//$I->see ( '"count":2' );
		$I->amOnPage ( "/rest/simple/orgas/list/?page[number]=1&page[size]=10" );
		$I->see ( 'lecnam.net' );
		$I->see ( 'unicaen.fr' );
		//$I->see ( '"count":4' );
		$I->amOnPage ( "/rest/simple/orgas/list/?page[number]=4" );
		$I->see ( 'lycee-sainte-ursule.com' );
		//$I->see ( '"count":1' );
		$I->amOnPage ( "/rest/simple/orgas/list/?page[number]=2&page[size]=2" );
		$I->see ( 'lycee-sainte-ursule.com' );
		$I->see ( 'IUT Campus III' );
		//$I->see ( '"count":2' );
		$I->amOnPage ( "/rest/simple/orgas/list/?page[number]=4&page[size]=2" );
		//$I->see ( '"count":0' );
	}

	// tests
	public function tryToGetAllAttributes(AcceptanceTester $I) {
		$I->amOnPage ( "/rest/simple/orgas/list/?filter=name like 'C*'&page[number]=1&page[size]=1" );
		$I->see ( 'lecnam.net' );
		//$I->see ( '"count":1' );
	}

	// tests
	public function tryToAddUpdateAndDelete(AcceptanceTester $I) {

		  $I->amOnPage ( "/RestTester" );
		  $uuid = uniqid ();
		  $I->fillField ( '#url', '/rest/simple/orgas/' );
		  $I->fillField ( '#method', 'post' );
		  $I->fillField ( '#datas', "{name:'microsoft" . $uuid . "',domain:'microsoft" . $uuid . ".com'}" );
		  $this->waitAndclick($I, "#btSubmitJSON" );
		  $I->waitForText ( 'inserted', self::TIMEOUT );
		  $I->waitForElement ( "#newId span", self::TIMEOUT );
		  $id = $I->grabTextFrom ( "#newId span" );
		  $uuid = uniqid ();
		  $I->fillField ( '#url', '/rest/simple/orgas/' . $id );
		  $I->fillField ( '#method', 'patch' );
		  $I->fillField ( '#datas', "{name:'microsoft" . $uuid . "',domain:'microsoft" . $uuid . ".com'}" );
		  $this->waitAndclick($I, "#btSubmitJSON" );
		  $I->waitForText ( 'updated', self::TIMEOUT );
		  $I->fillField ( '#method', 'delete' );
		  $this->waitAndclick($I,  "#btSubmitJSON" );
		  $I->waitForText ( 'deleted', self::TIMEOUT );
	}
}
