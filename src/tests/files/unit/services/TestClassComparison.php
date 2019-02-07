<?php

namespace services;

class TestClassComparison {

	/**
	 *
	 * @validator("equals","value")
	 */
	private $equalsValue;

	/**
	 *
	 * @validator("greaterThanOrEqual",100)
	 */
	private $greaterThanOrEquals100;

	/**
	 *
	 * @validator("greaterThan",100)
	 */
	private $greaterThan100;

	/**
	 *
	 * @validator("lessThanOrEqual",100)
	 */
	private $lessThanOrEquals100;

	/**
	 *
	 * @validator("lessThan",10)
	 */
	private $lessThan10;

	/**
	 *
	 * @validator("range",["min"=>2,"max"=>10])
	 */
	private $range2_10;

	public function __construct() {
		$this->equalsValue = "value";
		$this->greaterThan100 = 101;
		$this->greaterThanOrEquals100 = 100;
		$this->lessThan10 = 9;
		$this->lessThanOrEquals100 = 100;
		$this->range2_10 = 5;
	}

	/**
	 *
	 * @return string
	 */
	public function getEqualsValue() {
		return $this->equalsValue;
	}

	/**
	 *
	 * @return number
	 */
	public function getGreaterThanOrEquals100() {
		return $this->greaterThanOrEquals100;
	}

	/**
	 *
	 * @return number
	 */
	public function getGreaterThan100() {
		return $this->greaterThan100;
	}

	/**
	 *
	 * @return number
	 */
	public function getLessThanOrEquals100() {
		return $this->lessThanOrEquals100;
	}

	/**
	 *
	 * @return number
	 */
	public function getLessThan10() {
		return $this->lessThan10;
	}

	/**
	 *
	 * @return number
	 */
	public function getRange2_10() {
		return $this->range2_10;
	}

	/**
	 *
	 * @param string $equalsValue
	 */
	public function setEqualsValue($equalsValue) {
		$this->equalsValue = $equalsValue;
	}

	/**
	 *
	 * @param number $greaterThanOrEquals100
	 */
	public function setGreaterThanOrEquals100($greaterThanOrEquals100) {
		$this->greaterThanOrEquals100 = $greaterThanOrEquals100;
	}

	/**
	 *
	 * @param number $greaterThan100
	 */
	public function setGreaterThan100($greaterThan100) {
		$this->greaterThan100 = $greaterThan100;
	}

	/**
	 *
	 * @param number $lessThanOrEquals100
	 */
	public function setLessThanOrEquals100($lessThanOrEquals100) {
		$this->lessThanOrEquals100 = $lessThanOrEquals100;
	}

	/**
	 *
	 * @param number $lessThan10
	 */
	public function setLessThan10($lessThan10) {
		$this->lessThan10 = $lessThan10;
	}

	/**
	 *
	 * @param number $range0_10
	 */
	public function setRange2_10($range2_10) {
		$this->range2_10 = $range2_10;
	}
}

