<?php
$I = new FunctionalTester ( $scenario );
$I->wantTo ( 'perform actions and see result' );
$I->amOnPage ( "/TestUCookieController/index" );
$I->canSeeInSource ( "cookie" );

$I->amOnPage ( "/TestUCookieController/get" );
$I->canSeeInSource ( "not-exists" );

/*
 * $I->canSeeCookie ( "user" );
 * $I->canSeeCookie ( "other" );
 * $I->cantSeeCookie ( "notExist" );
 */

$I->amOnPage ( "/TestUCookieController/delete" );
$I->canSeeInSource ( "deleted" );
// $I->cantSeeCookie ( "user" );

$I->amOnPage ( "/TestUCookieController/deleteAll" );
$I->canSeeInSource ( "deleteds" );
//$I->cantSeeCookie ( "user" );
//$I->cantSeeCookie ( "other" );