<?php

namespace Ubiquity\exceptions;


/**
 * Normalizer Exceptions
 * @author jc
 *
 */
class NormalizerException extends UbiquityException{
	public function __construct($message=null,$code=0,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
