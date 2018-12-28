<?php

namespace Ubiquity\contents\validation\validators;

interface ValidatorInterface {
	/**
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value);
	
	public function getParameters():array;
}

