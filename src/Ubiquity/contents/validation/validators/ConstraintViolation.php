<?php

namespace Ubiquity\contents\validation\validators;

class ConstraintViolation {
	protected $message;
	protected $value;
	protected $member;
	protected $validatorType;
	protected $severity;
	
	public function __construct($message,$value,$member,$validatorType,$severity){
		$this->message=$message;
		$this->severity=$severity;
		$this->value=$value;
		$this->member=$member;
		$this->validatorType=$validatorType;
	}
}

