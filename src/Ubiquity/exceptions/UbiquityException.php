<?php

namespace Ubiquity\exceptions;


class UbiquityException extends \Exception{
	public function __construct($message=null,$code=0,$previous=null){
		parent::__construct($message, $code, $previous);
	}

	public function __toString(){
		return \get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
		. "{$this->getTraceAsString()}";
	}
}
