<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\utils\base\UString;
use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

class IsFalseValidator extends ValidatorHasNotNull {
	
	public function __construct(){
		$this->message="This value should return false";
	}
	public function validate($value) {
		parent::validate($value);
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

