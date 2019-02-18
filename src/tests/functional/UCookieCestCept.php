<?php
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions and see result' );
$I->amOnPage ( "/TestUCookieController/index" );
$I->canSeeInSource ( "cookie" );
$I->amOnPage ( "/TestUSessionController/testUser" );
$I->canSeeInSource ( "test-user" );
$I->amOnPage ( "/TestUSessionController/delete" );
$I->canSeeInSource ( "deleted" );
$I->amOnPage ( "/TestUSessionController/testDeleted" );
$I->canSeeInSource ( "no-user" );