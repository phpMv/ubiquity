<?php

namespace Ubiquity\events;

interface EventListenerInterface {
	public function on(&...$params);
}

