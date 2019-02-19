<?php
class BaseAcceptance {
	const TIMEOUT = 30;

	protected function waitAndclick(AcceptanceTester $I, $link, $context = null) {
		$element = $link;
		if ($context != null) {
			$element = $context . " " . $link;
		}
		$I->waitForElementClickable ( $element, self::TIMEOUT );
		$I->click ( $link, $context );
	}
}

