<?php
namespace micro\annotations;

use micro\utils\StrUtils;
require_once ROOT.DS.'micro/addendum/annotations.php';

class BaseAnnotation extends \Annotation {

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

	public function __toString(){
		$fields=$this->getPropertiesAndValues();
		$exts=array();
		$extsStr="";
		foreach ($fields as $k=>$v){
			if(StrUtils::isBoolean($v)===true){
				$exts[]=$k."=".StrUtils::getBooleanStr($v);
			}else{
				$exts[]=$k."=\"".$v."\"";
			}
		}
		if(\sizeof($exts)>0){
			$extsStr="(".\implode(",", $exts).")";
		}

		return "@".get_class($this).$extsStr;
	}
}