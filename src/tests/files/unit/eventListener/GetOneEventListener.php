<?php

namespace eventListener;

use Ubiquity\events\EventListenerInterface;
use Ubiquity\events\DAOEvents;

class GetOneEventListener implements EventListenerInterface {
	const EVENT_NAME = DAOEvents::GET_ONE;

	public function on(&...$params) {
		echo $params [0];
		echo $params [1];
	}
}

