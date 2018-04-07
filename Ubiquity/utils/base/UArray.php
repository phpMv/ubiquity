<?php

namespace Ubiquity\utils\base;

/**
 * Array utilities
 * @author jc
 *
 */
class UArray {

	/**
	 * Tests if array is associative
	 * @param array $array
	 * @return boolean
	 */
	public static function isAssociative($array) {
		return (array_keys($array) !== range(0, count($array) - 1));
	}
	
	/**
	 * Returns a new array with the keys $keys
	 * @param array $array an associative array
	 * @param array $keys some keys
	 * @return array
	 */
	public static function extractKeys($array,$keys){
		$result=[];
		foreach ($keys as $key){
			if(isset($array[$key])){
				$result[$key]=$array[$key];
			}
		}
		return $result;
	}

	public static function getValue($array, $key, $pos) {
		if (array_key_exists($key, $array)) {
			return $array[$key];
		}
		$values=array_values($array);
		if ($pos < sizeof($values))
			return $values[$pos];
	}

	public static function getDefaultValue($array, $key, $default) {
		if (array_key_exists($key, $array)) {
			return $array[$key];
		} else
			return $default;
	}

	public static function asPhpArray($array, $prefix="") {
		$exts=array ();
		$extsStr="";
		if (self::isAssociative($array)) {
			foreach ( $array as $k => $v ) {
				$exts[]="\"" . $k . "\"=>" . self::parseValue($v, 'array');
			}
		} else {
			foreach ( $array as $v ) {
				$exts[]=self::parseValue($v, 'array');
			}
		}
		if (\sizeof($exts) > 0 || $prefix !== "") {
			$extsStr="(" . \implode(",", $exts) . ")";
		}
		return $prefix . $extsStr;
	}

	public static function remove($array, $search) {
		if (\is_array($search)) {
			foreach ( $search as $val ) {
				$array=self::removeOne($array, $val);
			}
		} else {
			$array=self::removeOne($array, $search);
		}
		return array_values($array);
	}
	
	/**
	 * Removes from array by key
	 * @param array $array
	 * @param int|string $key
	 * @return array
	 */
	public static function removeByKey($array,$key){
		if(isset($array[$key])){
			unset($array[$key]);
		}
		return $array;
	}

	public static function removeOne($array, $search) {
		if (($key=array_search($search, $array)) !== false) {
			unset($array[$key]);
		}
		return $array;
	}

	public static function update(&$array, $search, $newValue) {
		if (($key=array_search($search, $array)) !== false) {
			$array[$key]=$newValue;
		}
		return $array;
	}

	private static function parseValue($v, $prefix="") {
		if (\is_bool($v) === true) {
			$result=UString::getBooleanStr($v);
		} elseif (\is_numeric($v)) {
			$result=$v;
		} elseif (\is_array($v)) {
			$result=self::asPhpArray($v, $prefix);
		} else {
			$result="\"" . \str_replace('$', '\$', $v) . "\"";
		}
		return $result;
	}
}
