<?php

namespace Ubiquity\contents\validation\validators\comparison;

use Ubiquity\contents\validation\validators\Validator;

class GreaterThanValidator extends Validator {
	
	protected $ref;
	
	public function __construct(){
		$this->message="This value should be greater than `{ref}`";
	}
	public function validate($value) {
		parent::validate($value);
		return $value>$this->ref;
	}
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["ref","value"];
	}

}

