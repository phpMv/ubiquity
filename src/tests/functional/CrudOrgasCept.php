<?php
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions on crud controllers and see result' );
$I->amOnPage ( "/TestCrudOrgas" );
$I->see ( "lecnam.net" );
$I->click ( "tr[data-ajax='1']", "#dataTable" );
$I->waitForText ( "users (12)", self::TIMEOUT, "body" );