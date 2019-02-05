<?php
const TIMEOUT = 30;
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions on crud controllers and see result' );
$I->amOnPage ( "/TestCrudOrgas" );
$I->see ( "lecnam.net" );
// Test relation objects
$I->click ( "tr[data-ajax='1']", "#dataTable" );
$I->canSee ( "users (12)", "body" );
// Test object insertion
$I->click ( "._edit[data-ajax='1']", "#dataTable" );
$I->canSee ( "Editing an existing object", "body" );
$I->fillField ( "[name='aliases']", "cnam-basse-normandie.fr;cnam.fr" );
$I->click ( "#action-modal-frmEdit-0" );
$I->canSee ( "Modifications were successfully saved", "body" );
$I->canSee ( "cnam-basse-normandie.fr;cnam.fr", "tr[data-ajax='1'] td[data-field='aliases']" );