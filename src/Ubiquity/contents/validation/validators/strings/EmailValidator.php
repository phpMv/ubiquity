<?php

namespace Ubiquity\contents\validation\validators\strings;

/**
 * Validates an email address
 * Usage @validator("email")
 *
 * @author jc
 * @version 1.0.0
 */
class EmailValidator extends RegexValidator {

	public function __construct() {
		$this->message = "{value} is not a valid email address";
		$this->ref = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
		$this->match = true;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::asUI()
	 */
	public function asUI(): array {
		return ['inputType'=>'email']+\array_merge_recursive(parent::asUI () , ['rules' => [ 'email' ]]);
	}
}

