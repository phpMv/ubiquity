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
		$I->amOnPage ( "/RestTester" );
		$I->fillField ( '#url', '/jsonapi/organizations/' );
		$I->fillField ( '#method', 'post' );
		$I->fillField ( '#contentType', 'application/json; charset=utf-8' );
		$I->fillField ( '#datas', "{data:{attributes:{name:'microsoftJSONAPI',domain:'microsoftJSONAPI.com'}}}" );
		$this->waitAndclick ( $I, "#btSubmit" );
		$I->waitForText ( 'inserted', self::TIMEOUT );
		$id = $I->grabTextFrom ( "#newId" );
		$I->fillField ( '#url', '/jsonapi/organizations/' . trim ( $id ) );
		$I->fillField ( '#method', 'patch' );
		$I->fillField ( '#datas', "{data:{attributes:{name:'microsoftJSONAPI2',domain:'microsoftJSONAPI2.com'}}}" );
		$this->waitAndclick ( $I, "#btSubmit" );
		$I->waitForText ( 'updated', self::TIMEOUT );
		$I->fillField ( '#method', 'delete' );
		$this->waitAndclick ( $I, "#btSubmit" );
		$I->waitForText ( 'deleted', self::TIMEOUT );
	}
}
