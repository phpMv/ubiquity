<?php

namespace Ubiquity\orm\core;

use Ubiquity\orm\parser\ConditionParser;

class ObjectsConditionParser {
	
	/**
	 * @var ConditionParser
	 */
	protected $conditionParser;
	/**
	 * @var array
	 */
	protected $objects;
	
	public function __construct(){
		$this->conditionParser=new ConditionParser();
		$this->objects=[];
	}
	
	public function addPartObject($object,$condition,$value){
		$this->objects[]=$object;
		return $this->conditionParser->addPart($condition, $value);
	}
	/**
	 * @return \Ubiquity\orm\parser\ConditionParser
	 */
	public function getConditionParser() {
		return $this->conditionParser;
	}

	/**
	 * @return array
	 */
	public function getObjects() {
		return $this->objects;
	}
	
	public function hasParam($value){
		return $this->conditionParser->hasParam($value);
	}
	
	public function compileParts($separator=" OR "){
		$this->conditionParser->compileParts($separator);
	}
	
	public function addObject($object){
		$this->objects[]=$object;
	}
	
	public function isFull(){
		return $this->conditionParser->countParts()>=PendingRelationsRequest::$MAX_ROW_COUNT;
	}
}

