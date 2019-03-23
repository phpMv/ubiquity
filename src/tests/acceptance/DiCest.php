<?php
class DiCest extends BaseAcceptance {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
		$I->amOnPage ( "/TestDiController/initCache" );
		$I->canSee ( "Models cache reset" );
	}

	// tests
	public function tryToInstanciate(AcceptanceTester $I) {
		$I->amOnPage ( "/TestDiController" );
		$I->see ( 'IService instanciationIAllService instanciationIAllService instanciation' );
	}

	// tests
	public function tryToAutowired(AcceptanceTester $I) {
		$I->amOnPage ( "/TestDiController/autowired" );
		$I->see ( 'IService instanciationIAllService instanciationIAllService instanciation' );
		$I->see ( 'do !:autowired:!' );
	}

	// tests
	public function tryToInject(AcceptanceTester $I) {
		$I->amOnPage ( "/TestDiController/injected" );
		$I->see ( 'IService instanciationIAllService instanciationIAllService instanciation' );
		$I->see ( 'do !:injected:!in IAllService' );
	}

	// tests
	public function tryToAllInject(AcceptanceTester $I) {
		$I->amOnPage ( "/TestDiController/allInjected" );
		$I->see ( 'IService instanciationIAllService instanciationIAllService instanciation' );
		$I->see ( 'do !:*injected:!in IAllService' );
	}
}
