<?php

namespace Ubiquity\utils\http\session;

class SessionObject {
	protected $value;
	protected $duration;
	protected $creationTime;

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
		if (! $this->isExpired ())
			return $this->value;
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
	 * @return mixed
	 */
	public function getCreationTime() {
		return $this->creationTime;
	}

	/**
	 *
	 * @param mixed $value
	 */
	public function setValue($value) {
		if ($value !== $this->value)
			$this->creationTime = time ();
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
	public function isExpired() {
		return \time () - $this->creationTime > $this->duration;
	}

	/**
	 *
	 * @return number
	 */
	public function getTimeout() {
		$timeout = $this->duration - (\time () - $this->creationTime);
		if ($timeout > 0)
			return $timeout;
		return 0;
	}
}

