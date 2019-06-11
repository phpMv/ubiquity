<?php
namespace controllers;

use Ubiquity\translation\TranslatorManager;

/**
 * Controller TestTranslations
 */
class TestTranslations extends ControllerBase {

	public function index() {
		TranslatorManager::start();
		$this->_index();
	}

	private function _index($nb) {
		$this->loadView("TestTranslations/index.html", compact('nb'));
	}

	public function changeLocale($locale = 'en_EN', $nb = 0) {
		TranslatorManager::start($locale);
		$this->_index($nb);
	}
}
