<?php
include_once 'tests/acceptance/BaseAcceptance.php';
class CrudOrgaCest extends BaseAcceptance {

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGotoCrudIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/TestCrudOrgas" );
		$I->see ( "lecnam.net" );
		// Test relation objects
		$I->amOnPage ( "/TestCrudOrgas/showDetail/1" );
		$I->waitForText ( "users (12)", self::TIMEOUT, "body" );
		// Test object updating
		$I->amOnPage ( "/TestCrudOrgas/edit/no/1" );
		$I->waitForText ( "Editing an existing object", self::TIMEOUT, "body" );
		$I->fillField ( "[name='aliases']", "cnam-basse-normandie.fr;cnam.fr" );
		$I->waitForElementClickable ( "#frm-add-update button.positive", self::TIMEOUT );
		$I->click ( "button.positive", "#frm-add-update" );
		$I->waitForText ( "Modifications were successfully saved", self::TIMEOUT, "body" );
		$I->canSee ( "cnam-basse-normandie.fr;cnam.fr", "tr[data-ajax='1'] td[data-field='aliases']" );
		// Test field updating
		$I->amOnPage ( "/TestCrudOrgas" );
		$I->see ( "lecnam.net" );
		$I->doubleClick ( "tr[data-ajax='3'] td[data-field='domain']" );
		$I->waitForElement ( "#frm-member-domain", self::TIMEOUT );
		$I->fillField ( "#frm-member-domain [name='domain']", "iutc3.unicaen2.fr" );
		$this->waitAndclick($I, "#btO" ,'body');
		$I->waitForText ( "iutc3.unicaen2.fr", self::TIMEOUT );
		$I->amOnPage ( "/TestCrudOrgas" );
		$I->see ( "iutc3.unicaen2.fr" );
	}

	// Tests
	public function tryToCrudAddNewAndDelete(AcceptanceTester $I) {
		$I->amOnPage ( "/TestCrudOrgas" );
		$this->waitAndclick ( $I, "#btAddNew" );
		$I->waitForText ( "New object creation", self::TIMEOUT );
		$I->fillField ( "#frmEdit [name='name']", "Organization name test" );
		$I->fillField ( "#frmEdit [name='domain']", "Organization domain test" );
		$I->fillField ( "#frmEdit [name='aliases']", "Organization aliases test" );
		$I->waitForElementClickable ( "#action-modal-frmEdit-0", self::TIMEOUT );
		$I->click ( "#action-modal-frmEdit-0" );
		$I->waitForText ( "Organization name test", self::TIMEOUT, "body" );
		$I->waitForElementClickable ( "tr:last-child button._delete", self::TIMEOUT );
		$I->click ( "tr:last-child button._delete" );
		$I->waitForText ( "Remove confirmation", self::TIMEOUT, "body" );
		$I->waitForElementClickable ( "#table-messages #bt-okay", self::TIMEOUT );
		$I->click ( "#bt-okay", "#table-messages" );
		$I->waitForText ( "Deletion of", self::TIMEOUT, "body" );
		$I->dontSee ( "Organization aliases test", "body" );
	}

	// Tests
	public function tryToGotoDisplay(AcceptanceTester $I) {
		$I->amOnPage ( "TestCrudOrgas/display/no/1" );
		$I->waitForText ( "Organizationsettingss", self::TIMEOUT, "body" );
		$I->canSee ( "cnam-basse-normandie.fr", "body" );
		// Test field updating
		$I->wait(self::TIMEOUT);
		$I->doubleClick ( "table._element td[data-field='aliases']" );
		$I->waitForElement ( "#frm-member-aliases", self::TIMEOUT );
		$I->fillField ( "[name='aliases']", "cnam-basse-normandie.fr;cnam.fr;theCnam.org" );
		$this->waitAndclick ( $I, "#btO",'body' );
		$I->waitForText ( "cnam-basse-normandie.fr;cnam.fr;theCnam.org", self::TIMEOUT );
		$I->amOnPage ( "/TestCrudOrgas" );
		$I->see ( "cnam-basse-normandie.fr;cnam.fr;theCnam.org" );
	}

	// Tests
	public function tryToSearchText(AcceptanceTester $I) {
		$I->amOnPage ( "/TestCrudOrgas" );
		$I->fillField ( "#search-lv", "le" );
		$I->pressKey ( "#search-lv", "\xEE\x80\x87" );
		$I->wait ( 5 );
		$I->waitForElement ( "#search-query", self::TIMEOUT );
		$I->dontSee ( "Université de Caen-Normandie", "#lv" );
		$I->dontSee ( "IUT Campus III", "#lv" );
		$I->canSee ( "Conservatoire National des Arts et Métiers", "#lv" );
		$I->canSee ( "Lycée Sainte-Ursule", "#lv" );
	}

	// Tests
	public function tryToDeleteOne(AcceptanceTester $I) {
		$I->amOnPage ( "TestCrudOrgas/display/no/1" );
		$I->waitForText ( "Organizationsettingss", self::TIMEOUT, "body" );
		$I->waitForElementClickable ( "#table-details #buttons a._delete", self::TIMEOUT );
		$I->click ( "a._delete", "#table-details #buttons" );
		$I->waitForText ( "Remove confirmation", self::TIMEOUT, "body" );
		$I->click ( "#bt-okay.negative" );
		$I->waitForText ( "Can not delete `lecnam.net`", self::TIMEOUT, "body" );

		$I->amOnPage ( "TestCrudOrgas" );
		$I->waitForElementClickable ( "button[data-ajax='4']._delete", self::TIMEOUT );
		$I->click ( "button[data-ajax='4']._delete" );
		$I->waitForText ( "Remove confirmation", self::TIMEOUT, "body" );
		$I->waitForElementClickable ( ".content #bt-okay", self::TIMEOUT );
		$I->click ( "#bt-okay", ".content" );
		$I->waitForText ( "Can not delete", self::TIMEOUT, "body" );
	}
}
