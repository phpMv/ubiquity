<?php

namespace Ubiquity\contents\validation\validators\multiples;

/**
 * Validate int identifiers (notNull positive integer)
 * @author jc
 */
class IdValidator extends ValidatorMultiple {
	
	public function __construct(){
		parent::__construct();
		$this->message=array_merge($this->message,[
				"positive"=>"This value must be positive",
				"type"=>"This value must be an integer"
		]);
	}
	
	public function validate($value) {
		if (!parent::validate($value)) {
			return false;
		}
		if(!is_integer($value)){
			$this->violation="type";
			return false;
		}
		if($value<=0){
			$this->violation="positive";
			return false;
		}
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value"];
	}

}

