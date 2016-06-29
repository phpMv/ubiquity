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
}