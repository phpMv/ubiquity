<?php

namespace Ubiquity\orm\parser;

use Ubiquity\utils\base\UArray;
use Ubiquity\orm\OrmUtils;
use Ubiquity\db\SqlUtils;

class ConditionParser {
	private $firstPart;
	private $condition;
	private $parts=[];
	private $params;
	
	public function __construct($condition=null,$firstPart=null){
		$this->condition=$condition;
		$this->firstPart=$firstPart;
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
				if($this->addParams($value)){
					$retArray[]=SqlUtils::$quote . $key . SqlUtils::$quote . " = ?";
				}
			}
			$this->condition=implode($separator, $retArray);
		}
	}
	
	private function addParams($value){
		if(!isset($this->params[$value])){
			return $this->params[$value]=true;
		}
		return false;
	}
	
	public function addPart($condition,$value){
		if($this->addParams($value)){
			$this->parts[]=$condition;
		}
	}
	
	public function addParts($condition,$values){
		foreach ($values as $value){
			if($this->addParams($value)){
				$this->parts[]=$condition;
			}
		}
	}
	
	public function compileParts($separator=" OR "){
		$this->condition=implode($separator, $this->parts);
	}
	
	private function parseKey($keyValues,$className){
		$condition=$keyValues;
		if (strrpos($keyValues, "=") === false && strrpos($keyValues, ">") === false && strrpos($keyValues, "<") === false) {
			if($this->addParams($keyValues)){
				$condition=SqlUtils::$quote. OrmUtils::getFirstKey($className) . SqlUtils::$quote."= ?";
			}
		}
		return $condition;
	}
	
	/**
	 * @return string
	 */
	public function getCondition() {
		if(!isset($this->firstPart))
			return $this->condition;
			$ret=$this->firstPart;
			if(isset($this->condition)){
				$ret.=" WHERE ".$this->condition;
			}
			return $ret;
	}
	
	/**
	 * @return mixed
	 */
	public function getParams() {
		if(is_array($this->params))
			return array_keys($this->params);
			return;
	}
	
	/**
	 * @param string $condition
	 */
	public function setCondition($condition) {
		$this->condition = $condition;
		return $this;
	}
	
	/**
	 * @param mixed $params
	 */
	public function setParams($params) {
		$this->params = $params;
		return $this;
	}
	
	public function limitOne(){
		$limit="";
		if(\stripos($this->condition, " limit ")===false)
			$limit=" limit 1";
			$this->condition.=$limit;
	}
	
	public static function simple($condition,$params){
		$cParser=new ConditionParser($condition);
		$cParser->addParams($params);
		return $cParser;
	}
	
}

