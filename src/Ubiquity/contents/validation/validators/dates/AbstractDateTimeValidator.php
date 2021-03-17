<?php

namespace Ubiquity\contents\validation\validators\dates;

use Ubiquity\contents\validation\validators\ConstraintViolation;
use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

abstract class AbstractDateTimeValidator extends ValidatorHasNotNull {
	protected $ref;
	protected $strict = true;
	protected $warnings = [ ];

	public function validate($value) {
		parent::validate ( $value );
		if ($this->notNull !== false) {
			$value = ( string ) $value;
			\DateTime::createFromFormat ( $this->ref, $value );
			$errors = \DateTime::getLastErrors ();
			foreach ( $errors ['warnings'] as $warning ) {
				$this->warnings [] = new ConstraintViolation ( $warning, $value, $this->member, get_class ( $this ), 'warning' );
			}
			return $errors ['error_count'] <= 0 && (! $this->strict || $errors ['warning_count'] <= 0);
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

	/**
	 *
	 * @return mixed
	 */
	public function getWarnings() {
		return $this->warnings;
	}

	public function hasWarnings() {
		return \count ( $this->warnings ) > 0;
	}
}

