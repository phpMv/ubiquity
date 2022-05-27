<?php

namespace Ubiquity\utils\http\session;

class SessionObject {
	protected $value;
	protected $duration;
	protected int $creationTime;

	public function __construct($value, $duration) {
		$this->value = $value;
		$this->duration = $duration;
		$this->creationTime = time ();
	}

	/**
	 *
	 * @return mixed
	 */
	public function getValue() {
		if (! $this->isExpired ()) {
			return $this->value;
		}
		return;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getDuration() {
		return $this->duration;
	}

	/**
	 *
	 * @return int
	 */
	public function getCreationTime(): int {
		return $this->creationTime;
	}

	/**
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function setValue($value) {
		if ($value !== $this->value) {
			$this->creationTime = time();
		}
		return $this->value = $value;
	}

	/**
	 *
	 * @param mixed $duration
	 */
	public function setDuration($duration) {
		$this->duration = $duration;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isExpired(): bool {
		return \time () - $this->creationTime > $this->duration;
	}

	/**
	 *
	 * @return int
	 */
	public function getTimeout(): int {
		$timeout = $this->duration - (\time () - $this->creationTime);
		if ($timeout > 0) {
			return $timeout;
		}
		return 0;
	}
}

