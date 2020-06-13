<?php

namespace Ubiquity\utils\http\session\protection;

/**
 * Ubiquity\utils\http\session\protection$VerifyCsrfInterface
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 */
interface VerifySessionCsrfInterface {

	public function init();

	public function start();

	public function clear();

	/**
	 * Get security level.
	 */
	public static function getLevel();
}

