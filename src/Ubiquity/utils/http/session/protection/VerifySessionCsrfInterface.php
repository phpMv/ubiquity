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
	 */
	public function init(): void;

	/**
	 * Called wjen the session is started.
	 */
	public function start(): void;

	/**
	 * Removes the actual csrftoken.
	 */
	public function clear(): void;

	/**
	 * Get security level.
	 * @return int
	 */
	public static function getLevel():int;
}

