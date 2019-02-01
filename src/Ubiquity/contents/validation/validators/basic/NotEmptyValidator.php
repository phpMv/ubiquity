<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\Validator;

class NotEmptyValidator extends Validator {
	
	public function __construct(){
		$this->message="This value should not be empty";
	}
	public function validate($value) {
		return $value!=null;
	}
}

