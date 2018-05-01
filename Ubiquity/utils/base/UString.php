<?php

namespace Ubiquity\utils\base;

/**
 * String utilities
 * @author jc
 * @version 1.0.0.2
 */
class UString {

	public static function startswith($hay, $needle) {
		return \substr($hay, 0, strlen($needle)) === $needle;
	}

	public static function endswith($hay, $needle) {
		return \substr($hay, -strlen($needle)) === $needle;
	}

	public static function getBooleanStr($value) {
		$ret="false";
		if ($value)
			$ret="true";
		return $ret;
	}

	public static function isNull($s) {
		return (!isset($s) || NULL === $s || "" === $s);
	}

	public static function isNotNull($s) {
		return (isset($s) && NULL !== $s && "" !== $s);
	}

	public static function isBooleanTrue($s) {
		return filter_var($s, FILTER_VALIDATE_BOOLEAN)===true;
	}

	public static function isBooleanFalse($s) {
		return $s === false || $s === "false" || $s === 0 || $s === "0";
	}

	public static function isBoolean($value) {
		return \is_bool($value);
	}
	
	public static function isBooleanStr($value) {
		return \is_bool($value) || $value===0 || $value===1 || $value==="on";
	}

	/**
	 * Pluralize an expression
	 * @param int $count the count of elements
	 * @param string $zero value to return if count==0, can contains {count} mask
	 * @param string $one value to return if count==1, can contains {count} mask
	 * @param string $other value to return if count>1, can contains {count} mask
	 * @return string the pluralized expression
	 */
	public static function pluralize($count, $zero, $one,$other) {
		$result="";
		if($count===0){
			$result=$zero;
		}elseif($count===1){
			$result=$one;
		}else{
			$result=$other;
		}
		return \str_replace('{count}', $count, $result);
	}

	public static function firstReplace($haystack, $needle, $replace) {
		$newstring=$haystack;
		$pos=strpos($haystack, $needle);
		if ($pos !== false) {
			$newstring=\substr_replace($haystack, $replace, $pos, strlen($needle));
		}
		return $newstring;
	}
	
	public static function replaceFirstOccurrence($pattern, $replacement, $subject){
		$pattern = '/'.preg_quote($pattern, '/').'/';
		return preg_replace($pattern, $replacement, $subject, 1);
	}

	public static function replaceArray($haystack, $needle, $replaceArray) {
		$result=$haystack;
		foreach ( $replaceArray as $replace ) {
			$result=self::firstReplace($result, $needle, $replace);
		}
		return $result;
	}
	
	public static function doubleBackSlashes($value){
		if(is_string($value))
			return str_replace("\\", "\\\\", $value);
		return $value;
	}

	public static function cleanAttribute($attr, $replacement="_") {
		$result=preg_replace('/[^a-zA-Z0-9\-]/s', $replacement, $attr);
		return \str_replace($replacement . $replacement, $replacement, $result);
	}
}

