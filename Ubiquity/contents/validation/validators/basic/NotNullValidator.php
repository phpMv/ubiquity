<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\Validator;

class NotNullValidator extends Validator {
	
	public function __construct(){
		$this->message="This value should not be null";
	}
	public function validate($value) {
		return $value!==null;
	}
}

