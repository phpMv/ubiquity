<?php

namespace Ubiquity\contents\validation\validators\strings;


use Ubiquity\contents\validation\validators\Validator;
use Ubiquity\exceptions\ValidatorException;

/**
 * Validates a string
 * with @validator("regex",pattern)
 * @author jc
 *
 */
class RegexValidator extends Validator {
	protected $ref;
	protected $match;
	
	public function __construct(){
		$this->message="This value is not valid";
		$this->match=true;
	}
	
	public function validate($value) {
		if (null === $value || '' === $value) {
			return;
		}
		if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
			throw new ValidatorException('This value can not be converted to string');
		}
		$value = (string) $value;
		return $this->match xor preg_match($this->ref, $value);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value"];
	}
}

