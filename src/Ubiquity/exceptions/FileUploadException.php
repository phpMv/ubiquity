<?php

namespace Ubiquity\exceptions;


/**
 * FileUpload Exceptions
 * @author jc
 *
 */
class FileUploadException extends UbiquityException{
	public function __construct($message=null,$code=0,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
