<?php
namespace micro\annotations;
use mindplay\annotations\standard\PropertyAnnotation;
use mindplay\annotations\Annotations;
use micro\utils\JArray;

/**
 * @usage('property'=>true, 'inherited'=>true)
 */
class BaseAnnotation extends PropertyAnnotation {

	public function getProperties(){
		$reflect = new \ReflectionClass($this);
		$props   = $reflect->getProperties();
		return $props;
	}

	public function getPropertiesAndValues($props=NULL){
		$ret=array();
		if(is_null($props))
			$props=$this->getProperties($this);
			foreach ($props as $prop){
				$prop->setAccessible(true);
				$v=$prop->getValue($this);
					if($v!==null && $v!=="" && isset($v)){
						$ret[$prop->getName()]=$v;
				}
			}
			return $ret;
	}

	public function asPhpArray(){
		$fields=$this->getPropertiesAndValues();
		return JArray::asPhpArray($fields);
	}

	public function __toString(){
		$extsStr=$this->asPhpArray();
		$className=get_class($this);
		$annotName=\substr($className, \strlen("micro\annotations\\"));
		$annotName = \substr($annotName, 0, \strlen($annotName)-\strlen("Annotation"));
		return "@".\lcfirst($annotName).$extsStr;
	}
}
