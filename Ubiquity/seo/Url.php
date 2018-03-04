<?php

namespace Ubiquity\seo;

/**
 * Url for Seo module, use for sitemap generation
 * @author jc
 *
 */
class Url {
	private $location;
	private $lastModified;
	private $changeFrequency;
	private $priority;

	public function __construct($location, $lastModified=null, $changeFrequency="daily", $priority="0.5") {
		$this->location=$location;
		$this->lastModified=$lastModified;
		$this->changeFrequency=$changeFrequency;
		$this->priority=$priority;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastModified() {
		return $this->lastModified;
	}

	/**
	 *
	 * @return string
	 */
	public function getChangeFrequency() {
		return $this->changeFrequency;
	}

	/**
	 *
	 * @return string
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 *
	 * @param mixed $location
	 */
	public function setLocation($location) {
		$this->location=$location;
	}

	/**
	 *
	 * @param string $lastModified
	 */
	public function setLastModified($lastModified) {
		$this->lastModified=$lastModified;
	}

	/**
	 *
	 * @param string $changeFrequency
	 */
	public function setChangeFrequency($changeFrequency) {
		$this->changeFrequency=$changeFrequency;
	}

	/**
	 *
	 * @param string $priority
	 */
	public function setPriority($priority) {
		$this->priority=$priority;
	}
}

