<?php

namespace controllers;

use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\http\URequest;

/**
 * Controller TestTranslations
 */
class TestTranslations extends ControllerBase {

	public function index() {
		TranslatorManager::start ( 'en_GB' );
		$this->_index ( TranslatorManager::fixLocale ( URequest::getDefaultLanguage () ) );
	}

	private function _index($nb) {
		$this->loadView ( "TestTranslations/index.html", compact ( 'nb' ) );
	}

	public function changeLocale($locale = 'en_GB', $nb = 0) {
		TranslatorManager::start ( $locale );
		$this->_index ( $nb );
	}
}
