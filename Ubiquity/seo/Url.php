<?php

namespace Ubiquity\seo;

use Ubiquity\utils\http\URequest;

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
	private $existing;
	private $valid;

	public function __construct($location="", $lastModified=null, $changeFrequency="daily", $priority="0.5") {
		$this->location=$location;
		$this->lastModified=$lastModified;
		$this->changeFrequency=$changeFrequency;
		$this->priority=$priority;
		$this->existing=false;
		$this->valid=true;
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
	/**
	 * @return mixed
	 */
	public function getExisting() {
		return $this->existing;
	}

	/**
	 * @param mixed $existing
	 */
	public function setExisting($existing) {
		$this->existing = $existing;
	}

	public static function fromArray($array,$existing=true){
		$array["existing"]=$existing;
		$object=new Url();
		URequest::setValuesToObject($object,$array);
		return $object;
	}
	/**
	 * @return boolean
	 */
	public function getValid() {
		return $this->valid;
	}

	/**
	 * @param boolean $valid
	 */
	public function setValid($valid) {
		$this->valid = $valid;
	}


}

