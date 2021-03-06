<?php

namespace Ubiquity\contents\validation\validators\multiples;

use Ubiquity\log\Logger;
use Ubiquity\contents\validation\validators\HasNotNullInterface;

/**
 * Validate Strings length using min, max, charset,notNull parameters.
 * For front and back-end validation
 *
 * Ubiquity\contents\validation\validators\multiples$LengthValidator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class LengthValidator extends ValidatorMultiple implements HasNotNullInterface {
	protected $min;
	protected $max;
	protected $charset = 'UTF-8';

	public function __construct() {
		parent::__construct ();
		$this->message = \array_merge ( $this->message, [ 'max' => 'This value must be at least {min} characters long','min' => 'This value cannot be longer than {max} characters','exact' => 'This value should have exactly {min} characters.',"charset" => "This value is not in {charset} charset" ] );
	}

	public function validate($value) {
		if (parent::validate ( $value ) === false) {
			return false;
		}
		if (! is_scalar ( $value ) && ! (\is_object ( $value ) && method_exists ( $value, '__toString' ))) {
			Logger::warn ( 'Validation', 'This value is not valid string for the charset ' . $this->charset );
			return true;
		}
		return $this->validateValue ( ( string ) $value );
	}

	protected function validateValue($stringValue) {
		if (@mb_check_encoding ( $stringValue, $this->charset )) {
			$length = mb_strlen ( $stringValue, $this->charset );
			if ($this->min === $this->max && $this->min !== null && $length !== $this->max) {
				$this->violation = 'exact';
				return false;
			} elseif ($this->max !== null && $length > $this->max) {
				$this->violation = 'max';
				return false;
			} elseif ($this->min !== null && $length < $this->min) {
				$this->violation = 'min';
				return false;
			}
			return true;
		} else {
			$this->violation = 'charset';
			return false;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return [ 'min','max','charset','value' ];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::asUI()
	 */
	public function asUI(): array {
		$rules=[];
		if ($this->min === $this->max && $this->min !== null) {
			$rules[] = ['type' => 'exactLength', 'prompt' => $this->_getMessage()['exact'], 'value' => $this->min];
		}else {
			if (isset($this->min)) {
				$rules[] = ['type' => 'minLength', 'prompt' => $this->_getMessage()['min'], 'value' => $this->min];
			}
			if (isset($this->max)) {
				$rules[] = ['type' => 'maxLength', 'prompt' => $this->_getMessage()['max'], 'value' => $this->max];
			}
		}
		return \array_merge_recursive(parent::asUI () , ['rules' => $rules]);
	}
}

