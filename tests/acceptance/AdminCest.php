<?php
class AdminCest {
	const TIMEOUT = 15;

	public function _before(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/blank.html" );
		 * $I->setCookie ( 'PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3' );
		 */
	}

	// tests
	public function tryToGotoIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/" );
		$I->seeElement ( 'body' );
		$I->see ( 'Ubiquity', [ 'css' => 'body' ] );
	}

	// tests
	public function tryToGotoAdminIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin/index" );
		$I->seeInCurrentUrl ( "Admin/index" );
		$I->see ( 'Used to perform CRUD operations on data', [ 'css' => 'body' ] );
	}

	private function gotoAdminModule(string $url, AcceptanceTester $I) {
		$I->amOnPage ( "/Admin/index" );
		$I->seeInCurrentUrl ( "Admin/index" );
		$I->click ( "a[href='" . $url . "']" );
		$I->waitForElementVisible ( "#content-header", self::TIMEOUT );
		$I->canSeeInCurrentUrl ( $url );
	}

	public function tryToGotoAdminModels(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Models", $I );
		$I->click ( "a[data-model='models.Connection']" );
		$I->waitForElementVisible ( "#btAddNew", self::TIMEOUT );
		$I->canSeeInCurrentUrl ( "/Admin/showModel/models.Connection" );
		$I->see ( 'organizations/display/4', "#lv td" );
		$I->click ( "button._edit[data-ajax='8']" );
		$I->waitForElementVisible ( "#modal-frmEdit-models-Connection", self::TIMEOUT );
		$I->canSee ( 'Editing an existing object', 'form' );
	}

	// tests
	public function tryGotoAdminRoutes(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Routes", $I );
	}

	// tests
	public function tryGotoAdminControllers(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Controllers", $I );
	}

	// tests
	public function tryGotoAdminCache(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Cache", $I );
	}

	// tests
	public function tryGotoAdminRest(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Rest", $I );
	}

	// tests
	public function tryGotoAdminConfig(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Config", $I );
	}

	// tests
	public function tryGotoAdminGit(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Git", $I );
	}

	// tests
	public function tryGotoAdminSeo(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Seo", $I );
	}

	// tests
	public function tryGotoAdminLogs(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Logs", $I );
	}

	// tests
	public function tryGotoAdminTranslate(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Translate", $I );
	}
}
