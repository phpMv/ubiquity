<?php

namespace Ubiquity\exceptions;


/**
 * FileUpload Exceptions
 * @author jc
 *
 */
class FileUploadException extends UbiquityException{
	public function __construct($message=null,$code=null,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
