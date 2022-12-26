<?php
include_once 'tests/acceptance/BaseAcceptance.php';

class RespositoriesCest extends BaseAcceptance {
	const TIMEOUT = 50;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToGetAll(AcceptanceTester $I) {
		$I->amOnPage("/users/");
		$I->see('Utilisateurs');
		$I->see('Benjamin Sherman');
		$I->see('Acton Carrillo');
	}

	// tests
	public function tryToGetOne(AcceptanceTester $I) {
		$I->amOnPage("/users/Solomon");
		$I->see('Utilisateur');
		$I->see('Solomon Tucker');
	}

	// tests
	public function tryToGetById(AcceptanceTester $I) {
		$I->amOnPage("/users/1");
		$I->see('Utilisateur');
		$I->see('Benjamin Sherman');
	}

	// tests
	public function tryToInsertAndDelete(AcceptanceTester $I) {
		$I->amOnPage("/users/Salome/Menard");
		$I->see('Utilisateur');
		//$I->see('Salome Menard');
	}
}