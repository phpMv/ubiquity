<?php

namespace Ubiquity\contents\validation\validators\comparison;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

/**
 * Check if a value is in a range
 *
 * Usage @validator("range",["min"=>minValue,"max"=>maxValue])
 *
 * @author jcheron <myaddressmail@gmail.com>
 *
 */
class RangeValidator extends ValidatorHasNotNull {
	protected $min;
	protected $max;

	public function __construct() {
		$this->message = "This value should be between `{min}` and `{max}`";
	}

	public function validate($value) {
		parent::validate ( $value );
		if ($this->notNull !== false) {
			return $value >= $this->min && $value <= $this->max;
		}
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return [ "min","max","value" ];
	}
}

