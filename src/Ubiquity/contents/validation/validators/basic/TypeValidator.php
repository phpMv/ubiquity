<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

class TypeValidator extends ValidatorHasNotNull {
	protected $ref;

	public function __construct() {
		$this->message = "The value {value} is not a valid {ref}.";
	}

	public function validate($value) {
		parent::validate ( $value );
		if ($this->notNull !== false) {
			$type = strtolower ( $this->ref );
			$type = 'boolean' == $type ? 'bool' : $type;
			$isFunction = 'is_' . $type;
			$ctypeFunction = 'ctype_' . $type;
			if (\function_exists ( $isFunction ) && $isFunction ( $value )) {
				return true;
			} elseif (\function_exists ( $ctypeFunction ) && $ctypeFunction ( $value )) {
				return true;
			} elseif ($value instanceof $this->ref) {
				return true;
			}
			return false;
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

