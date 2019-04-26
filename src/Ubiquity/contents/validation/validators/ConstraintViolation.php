<?php

/**
 * Validators definition
 */
namespace Ubiquity\contents\validation\validators;

/**
 * Constraint Violation Generated During Validation with the ValidatorsManager
 *
 * Ubiquity\contents\validation\validators$ConstraintViolation
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class ConstraintViolation {
	protected $message;
	protected $value;
	protected $member;
	protected $validatorType;
	protected $severity;

	public function __construct($message, $value, $member, $validatorType, $severity) {
		$this->message = $message;
		$this->severity = $severity;
		$this->value = $value;
		$this->member = $member;
		$this->validatorType = $validatorType;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getMember() {
		return $this->member;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getValidatorType() {
		return $this->validatorType;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getSeverity() {
		return $this->severity;
	}

	/**
	 *
	 * @param mixed $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 *
	 * @param mixed $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 *
	 * @param mixed $member
	 */
	public function setMember($member) {
		$this->member = $member;
	}

	/**
	 *
	 * @param mixed $validatorType
	 */
	public function setValidatorType($validatorType) {
		$this->validatorType = $validatorType;
	}

	/**
	 *
	 * @param mixed $severity
	 */
	public function setSeverity($severity) {
		$this->severity = $severity;
	}

	public function __toString() {
		return sprintf ( '%s : %s', $this->member, $this->message );
	}
}

