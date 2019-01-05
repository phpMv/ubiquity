<?php

namespace Ubiquity\contents\validation\validators\dates;

use Ubiquity\contents\validation\validators\Validator;
use Ubiquity\contents\validation\validators\ConstraintViolation;

abstract class AbstractDateTimeValidator extends Validator {
	
	protected $ref;
	protected $strict=true;
	
	protected $warnings=[];
	
	public function validate($value) {
		parent::validate($value);
		$value = (string) $value;
		\DateTime::createFromFormat($this->ref, $value);
		$errors = \DateTime::getLastErrors();
		foreach ($errors['warnings'] as $warning) {
			$this->warnings[]=new ConstraintViolation($warning, $value, $this->member, get_class($this), 'warning');
		}
		return $errors['error_count']<=0 && (!$this->strict || $errors['warning_count']<=0);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["ref","value"];
	}
	
	/**
	 * @return mixed
	 */
	public function getWarnings() {
		return $this->warnings;
	}
	
	public function hasWarnings(){
		return sizeof($this->warnings)>0;
	}


}

