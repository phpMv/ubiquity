<?php

namespace Ubiquity\orm\parser;

use Ubiquity\utils\base\UArray;
use Ubiquity\orm\OrmUtils;
use Ubiquity\db\SqlUtils;

class ConditionParser {
	private $condition;
	private $parts=[];
	private $params;
	
	public function __construct($condition=null){
		$this->condition=$condition;
	}
	
	public function addKeyValues($keyValues,$classname,$separator=" AND ") {
		if(!is_array($keyValues)){
			$this->condition=$this->parseKey($keyValues, $classname);
		}else{
			if(!UArray::isAssociative($keyValues)){
				if(isset($classname)){
					$keys=OrmUtils::getKeyFields($classname);
					$keyValues=\array_combine($keys, $keyValues);
				}
			}
			$retArray=array ();
			foreach ( $keyValues as $key => $value ) {
				$retArray[]=SqlUtils::$quote . $key . SqlUtils::$quote . " = ?";
				$this->params[]=$value;
			}
			$this->condition=implode($separator, $retArray);
		}
	}
	
	public function addPart($condition,$value){
		$this->parts[]=$condition;
		$this->params[]=$value;
	}
	
	public function compileParts($separator=" OR "){
		$this->condition=implode($separator, $this->parts);
	}
	
	private function parseKey($keyValues,$className){
		$condition=$keyValues;
		if (strrpos($keyValues, "=") === false && strrpos($keyValues, ">") === false && strrpos($keyValues, "<") === false) {
			$condition=SqlUtils::$quote. OrmUtils::getFirstKey($className) . SqlUtils::$quote."= ?";
			$this->params[]=$keyValues;
		}
		return $condition;
	}

	/**
	 * @return string
	 */
	public function getCondition() {
		return $this->condition;
	}

	/**
	 * @return mixed
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param string $condition
	 */
	public function setCondition($condition) {
		$this->condition = $condition;
	}

	/**
	 * @param mixed $params
	 */
	public function setParams($params) {
		$this->params = $params;
	}
	
	public function limitOne(){
		$limit="";
		if(\stripos($this->condition, " limit ")===false)
			$limit=" limit 1";
		$this->condition.=$limit;
	}

}

