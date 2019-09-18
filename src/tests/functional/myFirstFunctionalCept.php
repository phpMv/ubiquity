<?php
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions and see result' );
$I->amOnPage ( "/" );
$I->see ( "Ubiquity" );