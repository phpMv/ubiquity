<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\Validator;

class TypeValidator extends Validator {
	
	protected $ref;
	
	public function __construct(){
		$this->message="The value {value} is not a valid {ref}.";
	}
	
	public function validate($value) {
		if ($this->notNull!==false && (null === $value || '' === $value)) {
			return;
		}
		$type = strtolower($this->ref);
		$type = 'boolean' == $type?'bool':$type;
		$isFunction = 'is_'.$type;
		$ctypeFunction = 'ctype_'.$type;
		if (\function_exists($isFunction) && $isFunction($value)) {
			return true;
		} elseif (\function_exists($ctypeFunction) && $ctypeFunction($value)) {
			return true;
		} elseif ($value instanceof $this->ref) {
			return true;
		}
		return false;
	}
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["ref","value"];
	}

}

