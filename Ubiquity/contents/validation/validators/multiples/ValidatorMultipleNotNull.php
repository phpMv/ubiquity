<?php

namespace Ubiquity\contents\validation\validators\multiples;

abstract class ValidatorMultipleNotNull extends ValidatorMultiple {
	protected $notNull;
	public function __construct(){
		$this->message=[
				"notNull"=>"This value should not be null"
		];
	}
	
	public function validate($value) {
		if($this->notNull===true && null===$value){
			$this->violation="notNull";
			return false;
		}
		return true;
	}

}

