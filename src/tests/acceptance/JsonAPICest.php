<?php
class JsonAPICest extends BaseAcceptance {
	const TIMEOUT = 50;

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
	public function tryToGetWithInclude(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/user/1/?included=organization" );
		$I->see ( 'Benjamin' );
		$I->see ( 'unicaen.fr' );
	}

	// tests
	public function tryToGetLinks(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/links/" );
		$I->see ( 'links' );
		$I->see ( '\/jsonapi\/{resource}\/' );
	}

	// tests
	public function tryToGetManyToOne(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/user/1/relationships/organization/" );
		$I->see ( 'unicaen.fr' );
	}

	// tests
	public function tryToGetOneToMany(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/organization/1/relationships/users/" );
		$I->see ( 'wyatt.higgins' );
	}

	// tests
	public function tryToGetManyToMany(AcceptanceTester $I) {
		$I->amOnPage ( "/jsonapi/user/1/relationships/groupes/" );
		$I->see ( 'Auditeurs' );
	}

	// tests
	public function tryToAddUpdateAndDelete(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/RestTester" );
		 * $uuid = uniqid ();
		 * $I->fillField ( '#url', '/jsonapi/organizations/' );
		 * $I->fillField ( '#method', 'post' );
		 * $I->fillField ( '#contentType', 'application/json; charset=utf-8' );
		 * $I->fillField ( '#datas', "{data:{attributes:{name:'microsoft" . $uuid . "',domain:'microsoft" . $uuid . ".com'}}}" );
		 * $I->click ( "#btSubmitJSON" );
		 * $I->waitForText ( 'inserted', self::TIMEOUT );
		 * $I->waitForElement ( "#newId span", self::TIMEOUT );
		 * $uuid = uniqid ();
		 * $id = $I->grabTextFrom ( "#newId span" );
		 * $I->fillField ( '#url', '/jsonapi/organizations/' . trim ( $id ) );
		 * $I->fillField ( '#method', 'patch' );
		 * $I->fillField ( '#datas', "{data:{attributes:{name:'microsoft" . $uuid . "',domain:'microsoft" . $uuid . "'}}}" );
		 * $I->click ( "#btSubmitJSON" );
		 * $I->waitForText ( 'updated', self::TIMEOUT );
		 * $I->fillField ( '#method', 'delete' );
		 * $I->click ( "#btSubmitJSON" );
		 * $I->waitForText ( 'deleted', self::TIMEOUT );
		 */
	}
}
