<?php
include_once 'tests/acceptance/BaseAcceptance.php';

class RespositoriesCest extends BaseAcceptance {
	const TIMEOUT = 50;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGet(AcceptanceTester $I) {
		$I->amOnPage("/users/");
		$I->see('Utilisateurs');
		$I->see('Benjamin Sherman');
		$I->see('Acton Carrillo');
	}
}