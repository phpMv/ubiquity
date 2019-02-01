<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\utils\base\UString;
use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

class IsBooleanValidator extends ValidatorHasNotNull {
	
	public function __construct(){
		$this->message="This value should be a boolean";
	}
	public function validate($value) {
		parent::validate($value);
		return UString::isBooleanStr($value);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value"];
	}
}

