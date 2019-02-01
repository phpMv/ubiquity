<?php

namespace Ubiquity\contents\validation\validators\comparison;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

class LessThanValidator extends ValidatorHasNotNull {
	
	protected $ref;
	public function __construct(){
		$this->message="This value should be smaller than `{ref}`";
	}
	public function validate($value) {
		parent::validate($value);
		return $value<$this->ref;
	}
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["ref","value"];
	}

}

