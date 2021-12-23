<?php

namespace Ubiquity\exceptions;


/**
 * Transformer Exceptions
 * @author jc
 *
 */
class TransformerException extends UbiquityException{
	public function __construct($message=null,$code=0,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
