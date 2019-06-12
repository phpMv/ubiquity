<?php
class TransControllerCest {
	const TIMEOUT = 30;

	public function _before(AcceptanceTester $I) {
	}

	// tests
	public function tryToConnect(AcceptanceTester $I) {
		$I->amOnPage ( "/TestTranslations" );
		$I->see ( "Translation" );
		$I->see ( "no translation" );

		$I->amOnPage ( "/TestTranslations/changeLocale/fr_FR" );
		$I->see ( "Traduction" );
		$I->see ( "Aucune traduction" );

		$I->amOnPage ( "/TestTranslations/changeLocale/en_EN" );
		$I->see ( "Translation" );
		$I->see ( "no translation" );

		$I->amOnPage ( "/TestTranslations/changeLocale/fr_FR/1" );
		$I->see ( "Traduction" );
		$I->see ( "Une traduction" );

		$I->amOnPage ( "/TestTranslations/changeLocale/en_EN/1" );
		$I->see ( "Translation" );
		$I->see ( "One translation" );

		$I->amOnPage ( "/TestTranslations/changeLocale/fr_FR/5" );
		$I->see ( "Traduction" );
		$I->see ( "5 traductions" );

		$I->amOnPage ( "/TestTranslations/changeLocale/en_EN/10" );
		$I->see ( "Translation" );
		$I->see ( "10 translations" );

		$I->amOnPage ( "/TestTranslations/changeLocale/blop/5" );
		$I->see ( "trans" );
		$I->see ( "multi.trans" );
	}
}
