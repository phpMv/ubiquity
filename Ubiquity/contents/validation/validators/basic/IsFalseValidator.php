<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\Validator;
use Ubiquity\utils\base\UString;

class IsFalseValidator extends Validator {
	
	public function __construct(){
		$this->message="This value should return false";
	}
	public function validate($value) {
		return UString::isBooleanFalse($value);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value"];
	}
}

