<?php
namespace Ubiquity\exceptions;


/**
 * Exceptions for code checking
 * @author jc
 *
 */
class InvalidCodeException extends UbiquityException{
	public function __construct($message=null,$code=0,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
