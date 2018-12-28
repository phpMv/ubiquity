<?php

namespace Ubiquity\contents\validation\validators;

abstract class Validator implements ValidatorInterface{
	protected $modifiedMessage;
	protected $message;
	protected $member;
	protected $value;
	
	public function validate_($value,$member,$instance,$params,$severity='warning',$message=null){
		$this->value=$value;
		$this->setParams($params);
		if(!$this->validate($value)){
			$this->modifiedMessage=$message;
			return new ConstraintViolation($this->_getMessage(), $value, $member, get_class($this),$severity);
		}
		return true;
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

