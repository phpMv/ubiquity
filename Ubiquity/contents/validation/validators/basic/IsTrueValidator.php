<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\Validator;
use Ubiquity\utils\base\UString;

class IsTrueValidator extends Validator {
	
	public function __construct(){
		$this->message="This value should return true";
	}
	public function validate($value) {
		return UString::isBooleanTrue($value);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value"];
	}
}

