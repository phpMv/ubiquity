<?php
class CrudOrgaCest {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGotoIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/TestCrudOrgas" );
		$I->see ( "lecnam.net" );
		// Test relation objects
		$I->amOnPage ( "/TestCrudOrgas/showDetail/1" );
		$I->waitForText ( "users (12)", self::TIMEOUT, "body" );
		// Test object insertion
		$I->amOnPage ( "/TestCrudOrgas/edit/no/1" );
		$I->waitForText ( "Editing an existing object", self::TIMEOUT, "body" );
		$I->fillField ( "[name='aliases']", "cnam-basse-normandie.fr;cnam.fr" );
		$I->click ( "#action-modal-frmEdit-0" );
		$I->waitForText ( "Modifications were successfully saved", self::TIMEOUT, "body" );
		$I->canSee ( "cnam-basse-normandie.fr;cnam.fr", "tr[data-ajax='1'] td[data-field='aliases']" );
		$I->doubleClick ( "tr[data-ajax='3'] td[data-field='domain']" );
		$I->waitForElement ( "#frm-member-domain", self::TIMEOUT );
		$I->fillField ( "[name='domain']", "iutc3.unicaen.fr" );
		$I->click ( "#btO" );
		$I->waitForText ( "iutc3.unicaen.fr", self::TIMEOUT );
		$I->amOnPage ( "/TestCrudOrgas" );
		$I->see ( "iutc3.unicaen.fr" );
	}
}
