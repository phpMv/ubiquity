<?php

namespace Ubiquity\exceptions;


class DAOException extends UbiquityException{
	public function __construct($message=null,$code=null,$previous=null){
		parent::__construct($message, $code, $previous);
	}
}
