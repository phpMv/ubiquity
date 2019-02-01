<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\Validator;

class IsNullValidator extends Validator {
	
	public function __construct(){
		$this->message="This value should be null";
	}
	public function validate($value) {
		return $value===null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value"];
	}
}

