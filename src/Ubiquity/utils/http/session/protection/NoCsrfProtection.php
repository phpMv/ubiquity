<?php

namespace Ubiquity\utils\http\session\protection;

/**
 * To be Used only if no Csrf protection of the session is required.
 * with Startup::setSessionInstance(new PhpSession(new NoCsrfProtection())); in services.php
 * Ubiquity\utils\http\session\protection$NoCsrfProtection
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 */
class NoCsrfProtection implements VerifySessionCsrfInterface {

	public function init() {
	}

	public function start() {
	}

	public function clear() {
	}

	public static function getLevel() {
		return 0;
	}
}
