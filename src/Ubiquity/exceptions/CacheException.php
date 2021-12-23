<?php
namespace Ubiquity\exceptions;


/**
 * Exceptions for Cache
 * @author jc
 *
 */
class CacheException extends UbiquityException{
	public function __construct($message=null,$code=0,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
