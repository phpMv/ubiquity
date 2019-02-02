<?php
class AdminCest {
	const TIMEOUT = 20;

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
		$I->click ( "#bt-init-cache" );
		$I->waitForElementVisible ( "#divRoutes .ui.message.info", self::TIMEOUT );
		$I->canSee ( 'Router cache reset', '.ui.message.info' );
	}

	// tests
	public function tryGotoAdminControllers(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Controllers", $I );
		// Create a new controller
		$I->appendField ( "#frmCtrl [name='name']", 'TestController' );
		$I->click ( '#ck-lbl-ck-div-name' ); // Click on create associated view
		$I->click ( '#action-field-name' ); // Create the controller
		$I->waitForElementVisible ( "#msgGlobal", self::TIMEOUT );
		// Test controller creation
		$I->canSee ( 'TestController', '#msgGlobal' );
		$I->canSee ( 'controller has been created in', '#msgGlobal' );
		$I->canSee ( 'The default view associated has been created in', '#msgGlobal' );
		$I->click ( "#filter-bt" );
		$I->waitForElementVisible ( "#filtering-frm", self::TIMEOUT );
		$I->click ( "#cancel-btn" );
		// Create action in controller
		/*
		 * $I->moveMouseOver( "#dd-bt-controllers5CTestController" );
		 * $I->click ( "#dd-item-dd-bt-controllers5CTestController-0" );
		 * $I->waitForElementVisible ( "#modalNewAction", self::TIMEOUT );
		 * $I->appendField ( "#action", 'hello' );
		 * $I->appendField ( "#parameters", 'who="world"' );
		 * $I->appendField ( "#content", 'echo "Hello ".$who."!";' );
		 * $I->click ( "#action-modalNewAction-0" );
		 * $I->waitForText ( "hello", self::TIMEOUT, "#dtControllers-tr-controllers5CTestController" );
		 */

		$I->amOnPage ( "/TestController" );
		$I->canSeeInCurrentUrl ( "/TestController" );

		/*
		 * $I->amOnPage ( "/TestController/hello" );
		 * $I->canSeeInCurrentUrl ( "/TestController/hello" );
		 * $I->see ( 'Hello world!', [ 'css' => 'body' ] );
		 * $I->amOnPage ( "/TestController/hello/nobody" );
		 * $I->canSeeInCurrentUrl ( "/TestController/hello/nobody" );
		 * $I->see ( 'Hello nobody!', [ 'css' => 'body' ] );
		 */
	}

	// tests
	public function tryGotoAdminCache(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Cache", $I );
		$I->click ( "#ck-cacheTypes-4" );
		$I->waitForElement ( "#dd-type-Annotations" );
	}

	// tests
	public function tryGotoAdminRest(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Rest", $I );
		$I->canSee ( "Rest error", "body" );
		$I->click ( "#bt-init-rest-cache" );
		$I->waitForText ( "No resource Rest found. You can add a new resource.", self::TIMEOUT, "body" );
		// Add a new resource
		$I->click ( "#bt-new-resource" );
		$I->waitForText ( "Creating a new REST controller...", "body" );
		$I->appendField ( "#ctrlName", "RestUsersController" );
		$I->appendField ( "#route", "/rest/Users" );
		$I->click ( "#bt-create-new-resource" );
		$I->waitForText ( "controllers\RestUsersController", "body" );
		$I->amOnPage ( "/rest/Users" );
		$I->see ( '"count":101' );
	}

	// tests
	public function tryGotoAdminConfig(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Config", $I );
	}

	// tests
	/*
	 * public function tryGotoAdminGit(AcceptanceTester $I) {
	 * $this->gotoAdminModule ( "Admin/Git", $I );
	 * }
	 */
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
