<?php

namespace Ubiquity\contents\validation\validators;

use Ubiquity\exceptions\ValidatorException;
use Ubiquity\utils\base\UString;

abstract class ValidatorHasNotNull extends Validator implements HasNotNullInterface {
	protected $notNull;

	public function validate($value) {
		if ($this->notNull !== false && (null === $value || '' === $value)) {
			return;
		}
		if ($this->notNull === true && ! UString::isValid ( $value )) {
			throw new ValidatorException ( 'This value can not be converted to string' );
		}
	}

	public function asUI(): array {
		if ($this->notNull) {
			return [ 'rules' => [ 'empty' ] ];
		}
		return [ ];
	}
}

