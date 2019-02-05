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
		$I->fillField ( "#frmCtrl [name='name']", 'TestAcceptanceController' );
		$I->click ( '#ck-lbl-ck-div-name' ); // Click on create associated view
		$I->click ( '#action-field-name' ); // Create the controller
		$I->waitForElementVisible ( "#msgGlobal", self::TIMEOUT );
		// Test controller creation
		$I->canSee ( 'TestAcceptanceController', '#msgGlobal' );
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

		$I->amOnPage ( "/TestAcceptanceController" );
		$I->canSeeInCurrentUrl ( "/TestAcceptanceController" );

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
		$I->waitForElement ( "#dd-type-Annotations", self::TIMEOUT );
	}

	// tests
	public function tryGotoAdminRest(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Rest", $I );
		$I->canSee ( "Rest error", "body" );
		$I->click ( "#bt-init-rest-cache" );
		$I->waitForText ( "Rest service", self::TIMEOUT, "body" );
		// Add a new resource
		$I->click ( "#bt-new-resource" );
		$I->waitForText ( "Creating a new REST controller...", self::TIMEOUT, "body" );
		$I->fillField ( "#ctrlName", "RestUsersController" );
		$I->fillField ( "#route", "/rest/Users" );
		$I->click ( "#bt-create-new-resource" );
		$I->waitForText ( "controllers\RestUsersController", self::TIMEOUT, "body" );
		$I->wait ( 5 );
		$I->click ( "#bt-init-rest-cache" );
		$I->waitForText ( "/rest/Users/(index/)?", self::TIMEOUT, "body" );
		$I->amOnPage ( "/rest/Users" );
		$I->see ( '"count":101' );
		$I->amOnPage ( "/rest/Users/1" );
		$I->see ( 'Benjamin' );
		$I->amOnPage ( "/rest/Users/getOne/1/true" );
		$I->see ( 'Benjamin' );
		$I->see ( 'de Caen-Normandie' );
		$I->see ( 'Auditeurs' );
		$I->see ( 'myaddressmail@gmail.com' );
		$I->amOnPage ( "/rest/Users/getOne/500" );
		$I->see ( '{"message":"No result found","keyValues":"500"}' );
		$I->amOnPage ( "/rest/Users/connect/" );
		$I->see ( 'Bearer' );
		$I->amOnPage ( "/rest/Users/get/firstname+like+%27B%25%27" );
		$I->see ( '"count":7' );
	}

	// tests
	public function tryGotoAdminConfig(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Config", $I );
		$I->click ( '#edit-config-btn' );
		$I->waitForElement ( "#save-config-btn", self::TIMEOUT );
		$I->click ( "#save-config-btn" );
		$I->waitForElement ( "#edit-config-btn" );
		$I->see ( "http://dev.local/" );
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
		$I->click ( "#generateRobots" );
		$I->waitForText ( "Can not generate robots.txt if no SEO controller is selected.", self::TIMEOUT, "body" );
		$I->click ( "#addNewSeo" );
		$I->waitForText ( "Creating a new Seo controller", self::TIMEOUT, "body" );
		$I->fillField ( "#controllerName", "TestSEOController" );
		$I->click ( "#action-modalNewSeo-0" );
		$I->waitForText ( "The TestSEOController controller has been created" );
		$I->wait ( 5 );
		$this->gotoAdminModule ( "Admin/Seo", $I );
		$I->click ( "#seoCtrls-tr-controllersTestSEOController" );
		$I->waitForText ( "Change Frequency", self::TIMEOUT, "body" );
	}

	// tests
	public function tryGotoAdminLogs(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Logs", $I );
		$I->click ( "a._activateLogs" );
		$I->waitForElement ( "#maxLines" );
	}

	// tests
	public function tryGotoAdminTranslate(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Translate", $I );
	}
}
