<?php

namespace services;

class TestClassToValidate {

	/**
	 *
	 * @validator("notNull")
	 */
	private $notNull;

	/**
	 *
	 * @validator("isNull")
	 */
	private $isNull;

	/**
	 *
	 * @validator("notEmpty")
	 */
	private $notEmpty;

	/**
	 *
	 * @validator("isBool")
	 */
	private $bool;

	public function __construct() {
		$this->notNull = "plein";
		$this->bool = true;
		$this->isNull = null;
		$this->notEmpty = "pas vide";
	}

	/**
	 *
	 * @return mixed
	 */
	public function getNotNull() {
		return $this->notNull;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getIsNull() {
		return $this->isNull;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getBool() {
		return $this->bool;
	}

	/**
	 *
	 * @param mixed $notNull
	 */
	public function setNotNull($notNull) {
		$this->notNull = $notNull;
	}

	/**
	 *
	 * @param mixed $isNull
	 */
	public function setIsNull($isNull) {
		$this->isNull = $isNull;
	}

	/**
	 *
	 * @param mixed $bool
	 */
	public function setBool($bool) {
		$this->bool = $bool;
	}

	/**
	 *
	 * @return string
	 */
	public function getNotEmpty() {
		return $this->notEmpty;
	}

	/**
	 *
	 * @param string $notEmpty
	 */
	public function setNotEmpty($notEmpty) {
		$this->notEmpty = $notEmpty;
	}
}

