<?php

namespace services;

class Service {

	public function __construct($controller) {
		echo "service init!";
	}

	public function __toString() {
		return 'service';
	}
}

