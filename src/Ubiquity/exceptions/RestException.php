<?php
namespace Ubiquity\exceptions;


/**
 * Exceptions for Rest service
 * @author jc
 *
 */
class RestException extends UbiquityException{
	public function __construct($message=null,$code=0,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
