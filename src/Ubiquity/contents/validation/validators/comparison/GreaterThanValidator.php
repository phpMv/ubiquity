<?php

namespace Ubiquity\contents\validation\validators\comparison;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

class GreaterThanValidator extends ValidatorHasNotNull {
	protected $ref;

	public function __construct() {
		$this->message = "This value should be greater than `{ref}`";
	}

	public function validate($value) {
		parent::validate ( $value );
		if ($this->notNull !== false) {
			return $value > $this->ref;
		}
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return [ "ref","value" ];
	}
}

