<?php

namespace Ubiquity\contents\validation\validators\strings;


use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

/**
 * Validates a string with a regex
 * Usage @validator("regex",pattern)
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 */
class RegexValidator extends ValidatorHasNotNull {
	protected $ref;
	protected $match;
	
	public function __construct(){
		$this->message="This value is not valid";
		$this->match=true;
	}
	
	public function validate($value) {
		parent::validate($value);
		$value = (string) $value;
		return !($this->match xor preg_match($this->ref, $value));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value"];
	}
}

