<?php

namespace Ubiquity\exceptions;


/**
 * Validator Exceptions
 * @author jc
 *
 */
class ValidatorException extends UbiquityException{
	public function __construct($message=null,$code=null,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
