<?php

namespace Ubiquity\utils\base;

/**
 * String utilities
 *
 * Ubiquity\utils\base$UString
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
class UString {

	public static function startswith($hay, $needle) {
		return \substr ( $hay, 0, strlen ( $needle ) ) === $needle;
	}

	public static function contains($needle, $haystack) {
		return \strpos ( $haystack, $needle ) !== false;
	}

	public static function endswith($hay, $needle) {
		return \substr ( $hay, - strlen ( $needle ) ) === $needle;
	}

	public static function getBooleanStr($value) {
		return ($value === true || $value === 'true' || $value == 1) ? 'true' : 'false';
	}

	public static function isNull($s) {
		return (! isset ( $s ) || NULL === $s || '' === $s);
	}

	public static function isNotNull($s) {
		return (isset ( $s ) && NULL !== $s && '' !== $s);
	}

	public static function isBooleanTrue($s) {
		return filter_var ( $s, FILTER_VALIDATE_BOOLEAN ) === true;
	}

	public static function isBooleanFalse($s) {
		return \filter_var ( $s, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) === false;
	}

	public static function isBoolean($value) {
		return \is_bool ( $value );
	}

	public static function isBooleanStr($value) {
		return filter_var ( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) !== NULL;
	}

	/**
	 * Pluralize an expression
	 *
	 * @param int $count the count of elements
	 * @param string $zero value to return if count==0, can contains {count} mask
	 * @param string $one value to return if count==1, can contains {count} mask
	 * @param string $other value to return if count>1, can contains {count} mask
	 * @return string the pluralized expression
	 */
	public static function pluralize($count, $zero, $one, $other) {
		$result = $other;
		if ($count === 0) {
			$result = $zero;
		} elseif ($count === 1) {
			$result = $one;
		}
		return \str_replace ( '{count}', $count, $result );
	}

	public static function firstReplace($haystack, $needle, $replace) {
		$newstring = $haystack;
		$pos = \strpos ( $haystack, $needle );
		if ($pos !== false) {
			$newstring = \substr_replace ( $haystack, $replace, $pos, \strlen ( $needle ) );
		}
		return $newstring;
	}

	public static function replaceFirstOccurrence($pattern, $replacement, $subject) {
		$pattern = '/' . \preg_quote ( $pattern, '/' ) . '/';
		return \preg_replace ( $pattern, $replacement, $subject, 1 );
	}

	public static function replaceArray($haystack, $needleArray, $replace) {
		$result = $haystack;
		foreach ( $needleArray as $needle ) {
			$result = self::firstReplace ( $result, $needle, $replace );
		}
		return $result;
	}

	public static function doubleBackSlashes($value) {
		if (\is_string ( $value ))
			return \str_replace ( "\\", "\\\\", $value );
		return $value;
	}

	public static function cleanAttribute($attr, $replacement = "-") {
		$attr = \preg_replace ( '/[^a-zA-Z0-9\-]/s', $replacement, $attr );
		while ( $attr !== ($attr = \str_replace ( $replacement . $replacement, $replacement, $attr )) )
			;
		return $attr;
	}

	public static function mask($secretString, $maskChar = "*") {
		return \str_repeat ( $maskChar, \strlen ( $secretString ) );
	}

	public static function isValid($value) {
		return \is_scalar ( $value ) || (\is_object ( $value ) && \method_exists ( $value, '__toString' ));
	}

	public static function isJson($value) {
		return \is_object ( \json_decode ( $value ) );
	}

	/**
	 * Converts a value to a string
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function toString($value) {
		if (self::isValid ( $value )) {
			return $value . '';
		}
		return '';
	}

	/**
	 * Explodes a string with an array of delimiters
	 *
	 * @param array $delimiters
	 * @param string $string
	 * @return array
	 */
	public static function explode($delimiters, $string) {
		return \explode ( $delimiters [0], \str_replace ( $delimiters, $delimiters [0], $string ) );
	}
}

