<?php

namespace micro\utils;

/**
 * Utilitaires liés aux chaînes
 * @author jc
 * @version 1.0.0.1
 */
class StrUtils {

	public static function startswith($hay, $needle) {
		return substr($hay, 0, strlen($needle)) === $needle;
	}

	public static function endswith($hay, $needle) {
		return substr($hay, -strlen($needle)) === $needle;
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
		return $s === true || $s === "true" || $s === 1 || $s === "1";
	}

	public static function isBooleanFalse($s) {
		return $s === false || $s === "false" || $s === 0 || $s === "0";
	}

	public static function isBoolean($value) {
		return \is_bool($value);
	}

	public static function pluralize($count, $caption, $plural=NULL) {
		if ($plural == NULL) {
			$pluralChar="s";
			if (self::endswith($caption, "au")) {
				$pluralChar="x";
			}
			$plural=$caption . $pluralChar;
		}
		switch($count) {
			case 0:
				$result="aucun " . $caption;
				break;
			case 1:
				$result=$count . " " . $caption;
				break;
			default:
				$result=$count . " " . $plural;
				break;
		}
		return $result;
	}

	public static function firstReplace($haystack, $needle, $replace) {
		$newstring=$haystack;
		$pos=strpos($haystack, $needle);
		if ($pos !== false) {
			$newstring=substr_replace($haystack, $replace, $pos, strlen($needle));
		}
		return $newstring;
	}

	public static function replaceArray($haystack, $needle, $replaceArray) {
		$result=$haystack;
		foreach ( $replaceArray as $replace ) {
			$result=self::firstReplace($result, $needle, $replace);
		}
		return $result;
	}
}

