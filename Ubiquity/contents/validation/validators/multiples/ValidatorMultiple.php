<?php

namespace Ubiquity\contents\validation\validators\multiples;

use Ubiquity\contents\validation\validators\Validator;

abstract class ValidatorMultiple extends Validator{

	protected $violation;
	
	/**
	 * @return array|string
	 */
	protected function mergeMessages(){
		if(!isset($this->modifiedMessage)){
			return $this->message;
		}else{
			if(is_array($this->modifiedMessage) && is_array($this->message)){
				return array_merge($this->message,$this->modifiedMessage);
			}else{
				return $this->modifiedMessage;
			}
		}
	}
	
	protected function _getMessage(){
		$parameters=$this->getParameters();
		$message=$this->mergeMessages();
		if(isset($this->violation) && is_array($message)){
			$message=$this->_getMessageViolation($message);
		}
		foreach ($parameters as $param){
			$message=str_replace("{".$param."}", $this->$param, $message);
		}
		return $message;
	}
	
	protected function _getMessageViolation($messages){
		if(isset($messages[$this->violation])){
			return $messages[$this->violation];
		}
		return reset($messages);
	}


}

