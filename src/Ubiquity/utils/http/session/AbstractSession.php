<?php

namespace Ubiquity\utils\http\session;

use Ubiquity\utils\http\session\protection\VerifySessionCsrfInterface;
use Ubiquity\utils\http\session\protection\VerifyCsrfToken;

/**
 * Ubiquity\utils\http\session$AbstractSession
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.3
 *
 */
abstract class AbstractSession {
	protected ?string $name;
	protected VerifySessionCsrfInterface $verifyCsrf;

	public function __construct(?VerifySessionCsrfInterface $verifyCsrf = null) {
		$this->verifyCsrf = ($verifyCsrf ??= new VerifyCsrfToken ( $this ));
	}

	abstract public function get(string $key, $default = null);

	abstract public function set(string $key, $value);

	abstract public function terminate(): void;

	abstract public function start(string $name = null);

	abstract public function isStarted(): bool;

	abstract public function exists(string $key): bool;

	abstract public function getAll(): array;

	abstract public function delete(string $key);

	abstract public function visitorCount(): int;
	
	/**
	 * Re-generates the session id.
	 * @param boolean $deleteOldSession if true, deletes the old session
	 * @return bool
	 */
	public function regenerateId(bool $deleteOldSession=false): bool {
		return false;
	}

	public function getVerifyCsrf(): VerifySessionCsrfInterface {
		return $this->verifyCsrf;
	}
}

