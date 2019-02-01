<?php

namespace Ubiquity\contents\validation\validators;


/**
 * Abstract class for validators
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 */
abstract class Validator implements ValidatorInterface{
	protected $modifiedMessage;
	protected $message;
	protected $member;
	protected $value;
	protected $severity;

	
	/**
	 * @param mixed $value
	 * @return \Ubiquity\contents\validation\validators\ConstraintViolation|boolean
	 */
	public function validate_($value){
		$this->value=$value;
		if(!$this->validate($value)){
			return new ConstraintViolation($this->_getMessage(), $value, $this->member, get_class($this),$this->severity);
		}
		return true;
	}
	
	public function setValidationParameters($member,$params,$severity='warning',$message=null){
		$this->setParams($params);
		$this->member=$member;
		$this->modifiedMessage=$message;
		$this->severity=$severity;
	}
	
	protected function setParams(array $params){
		foreach ($params as $member=>$value){
			$this->$member=$value;
		}
	}
	
	/**
	 * @return mixed
	 */
	public function getMember() {
		return $this->member;
	}

	/**
	 * @param mixed $member
	 */
	public function setMember($member) {
		$this->member = $member;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\ValidatorInterface::getParameters()
	 */
	public function getParameters(): array {
		return [];
		
	}
	
	/**
	 * @return array|string
	 */
	protected function mergeMessages(){
		if(!isset($this->modifiedMessage)){
			return $this->message;
		}else{
			return $this->modifiedMessage;
		}
	}
	
	protected function _getMessage(){
		$parameters=$this->getParameters();
		$message=$this->mergeMessages();
		foreach ($parameters as $param){
			$message=str_replace("{".$param."}", $this->$param, $message);
		}
		return $message;
	}

}

