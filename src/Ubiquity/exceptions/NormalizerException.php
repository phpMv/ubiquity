<?php

namespace Ubiquity\exceptions;


/**
 * Normalizer Exceptions
 * @author jc
 *
 */
class NormalizerException extends UbiquityException{
	public function __construct($message=null,$code=null,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
