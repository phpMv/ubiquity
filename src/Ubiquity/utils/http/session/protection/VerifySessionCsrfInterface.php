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

	/**
	 * Creates the Csrf token and adds it to the session.
	 * @return mixed
	 */
	public function init();

	/**
	 * Called wjen the session is started.
	 * @return mixed
	 */
	public function start();

	/**
	 * Removes the actual csrftoken.
	 * @return mixed
	 */
	public function clear();

	/**
	 * Get security level.
	 * @return int
	 */
	public static function getLevel():int;
}

