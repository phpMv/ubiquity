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

	/**
	 *
	 * @validator("isFalse")
	 */
	private $isFalse;

	/**
	 *
	 * @validator("isTrue")
	 */
	private $isTrue;

	/**
	 *
	 * @validator("isEmpty")
	 */
	private $isEmpty;

	/**
	 *
	 * @validator("type","services\\Service")
	 */
	private $type;

	public function __construct() {
		$this->notNull = "plein";
		$this->bool = true;
		$this->isNull = null;
		$this->notEmpty = "pas vide";
		$this->isEmpty = '';
		$this->isFalse = false;
		$this->isTrue = true;
		$this->type = new Service ( null );
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

	/**
	 *
	 * @return boolean
	 */
	public function getIsFalse() {
		return $this->isFalse;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getIsTrue() {
		return $this->isTrue;
	}

	/**
	 *
	 * @return string
	 */
	public function getIsEmpty() {
		return $this->isEmpty;
	}

	/**
	 *
	 * @return \services\Service
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @param boolean $isFalse
	 */
	public function setIsFalse($isFalse) {
		$this->isFalse = $isFalse;
	}

	/**
	 *
	 * @param boolean $isTrue
	 */
	public function setIsTrue($isTrue) {
		$this->isTrue = $isTrue;
	}

	/**
	 *
	 * @param string $isEmpty
	 */
	public function setIsEmpty($isEmpty) {
		$this->isEmpty = $isEmpty;
	}

	/**
	 *
	 * @param \services\Service $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
}

