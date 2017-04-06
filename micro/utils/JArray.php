<?php

namespace micro\utils;

class JArray {

	public static function isAssociative($array) {
		return (array_keys($array)!==range(0, count($array)-1));
	}

	public static function getValue($array, $key, $pos) {
		if (array_key_exists($key, $array)) {
			return $array [$key];
		}
		$values=array_values($array);
		if ($pos<sizeof($values))
			return $values [$pos];
	}

	public static function getDefaultValue($array, $key, $default) {
		if (array_key_exists($key, $array)) {
			return $array [$key];
		} else
			return $default;
	}

	public static function asPhpArray($array,$prefix=""){
		$exts=array();
		$extsStr="";
		if(self::isAssociative($array)){
			foreach ($array as $k=>$v){
					$exts[]="\"".$k."\"=>".self::parseValue($v,$prefix);
			}
		}else{
			foreach ($array as $v){
				$exts[]=self::parseValue($v,$prefix);
			}
		}
		if(\sizeof($exts)>0 || $prefix!==""){
			$extsStr="(".\implode(",", $exts).")";
		}
		return $prefix.$extsStr;
	}

	private static function parseValue($v,$prefix=""){
		if(\is_bool($v)===true){
			$result=StrUtils::getBooleanStr($v);
		}elseif(\is_array($v)){
			$result=self::asPhpArray($v,$prefix);
		}
		else{
			$result="\"".$v."\"";
		}
		return $result;
	}
}
