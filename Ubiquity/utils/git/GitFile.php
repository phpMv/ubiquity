<?php

namespace Ubiquity\utils\git;

class GitFile {
	private $name;
	private $status;
	public function __construct($name="",$status=""){
		$this->name=$name;
		$this->status=$status;
	}
	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

}

