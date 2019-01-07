<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\utils\base\UString;
use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

class IsTrueValidator extends ValidatorHasNotNull {
	
	public function __construct(){
		$this->message="This value should return true";
	}
	public function validate($value) {
		parent::validate($value);
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

