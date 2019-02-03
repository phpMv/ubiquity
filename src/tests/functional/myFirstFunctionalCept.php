<?php
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions and see result' );
$I->amOnPage ( "/" );
$I->see ( "Ubiquity" );
$I->amOnPage ( "/Admin" );
$I->see ( "UbiquityMyAdmin" );
$I->amOnPage ( "/Admin/Models" );
$I->see("Used to perform CRUD operations on data.","body");