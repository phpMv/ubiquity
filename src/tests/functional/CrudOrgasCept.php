<?php
const TIMEOUT = 30;
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions on crud controllers and see result' );
$I->amOnPage ( "/TestCrudOrgas" );
$I->see ( "lecnam.net" );
// Test relation objects
$I->click ( "tr[data-ajax='1']", "#dataTable" );
$I->waitForText ( "users (12)", TIMEOUT, "body" );
// Test object insertion
$I->click ( "._edit[data-ajax='1']", "#dataTable" );
$I->waitForText ( "Editing an existing object", TIMEOUT, "body" );
$I->fillField ( "[name='aliases']", "cnam-basse-normandie.fr;cnam.fr" );
$I->click ( "#action-modal-frmEdit-0" );
$I->waitForText ( "Modifications were successfully saved", TIMEOUT, "body" );
$I->canSee ( "cnam-basse-normandie.fr;cnam.fr", "tr[data-ajax='1'] td[data-field='aliases']" );