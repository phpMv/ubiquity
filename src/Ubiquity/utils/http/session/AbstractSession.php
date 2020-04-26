<?php

namespace Ubiquity\utils\http\session;

use Ubiquity\utils\http\session\protection\VerifySessionCsrfInterface;
use Ubiquity\utils\http\session\protection\VerifyCsrfToken;

/**
 * Ubiquity\utils\http\session$AbstractSession
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.0
 *
 */
abstract class AbstractSession {
	protected $name;
	protected $verifyCsrf;

	public function __construct(?VerifySessionCsrfInterface $verifyCsrf = null) {
		$this->verifyCsrf = ($verifyCsrf ??= new VerifyCsrfToken ( $this ));
	}

	abstract public function get($key, $default = null);

	abstract public function set($key, $value);

	abstract public function terminate();

	abstract public function start($name = null);

	abstract public function isStarted();

	abstract public function exists($key);

	abstract public function getAll();

	abstract public function delete($key);
}

