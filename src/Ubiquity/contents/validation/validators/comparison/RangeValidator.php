<?php

namespace Ubiquity\contents\validation\validators\comparison;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

/**
 * @author jcheron <myaddressmail@gmail.com>
 *
 */
class RangeValidator extends ValidatorHasNotNull {
	
	protected $min;
	protected $max;
	
	public function __construct(){
		$this->message="This value should be between `{min}` and `{max}`";
	}
	
	public function validate($value) {
		parent::validate($value);
		return $value>=$this->min && $value<=$this->max;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["min","max","value"];
	}

}

