<?php
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions on crud controllers and see result' );
$I->amOnPage ( "/TestCrudOrgas" );
$I->see ( "lecnam.net" );
