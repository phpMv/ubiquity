<?php

namespace eventListener;

use Ubiquity\events\EventListenerInterface;
use Ubiquity\utils\http\URequest;
use Ubiquity\translation\TranslatorManager;

class DefineLocaleEventListener implements EventListenerInterface {
	const EVENT_NAME = 'defineLocale';

	public function on(&...$params) {
		$locales = [ 'fr_FR','en_GB','aa_BB' ];
		$locale = $locales [rand ( 0, 2 )];
		URequest::setLocale ( $locale );
		TranslatorManager::setLocale ( $locale );
	}
}

