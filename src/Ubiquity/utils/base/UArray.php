<?php

namespace Ubiquity\utils\base;

use Ubiquity\utils\base\traits\UArrayAsTrait;

/**
 * Array utilities.
 * Ubiquity\utils\base$UArray
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.6
 *
 */
class UArray {
	use UArrayAsTrait;

	/**
	 * Tests if array is associative
	 *
	 * @param array $array
	 * @return boolean
	 */
	public static function isAssociative($array) {
		return (\array_keys ( $array ) !== \range ( 0, \count ( $array ) - 1 ));
	}

	/**
	 * Returns a new array with the keys $keys
	 *
	 * @param array $array an associative array
	 * @param array $keys some keys
	 * @return array
	 */
	public static function extractKeys($array, $keys) {
		$result = [ ];
		foreach ( $keys as $key ) {
			if (isset ( $array [$key] )) {
				$result [$key] = $array [$key];
			}
		}
		return $result;
	}

	/**
	 *
	 * @param array $array
	 * @param string $key
	 * @param int $pos
	 * @return mixed|null
	 */
	public static function getValue($array, $key, $pos) {
		if (\array_key_exists ( $key, $array )) {
			return $array [$key];
		}
		$values = \array_values ( $array );
		if ($pos < \count ( $values ))
			return $values [$pos];
	}

	/**
	 *
	 * @param array $array
	 * @param string|int $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getRecursive($array, $key, $default = null) {
		if (\array_key_exists ( $key, $array )) {
			return $array [$key];
		} else {
			foreach ( $array as $item ) {
				if (is_array ( $item )) {
					return self::getRecursive ( $item, $key, $default );
				}
			}
		}
		return $default;
	}

	public static function getDefaultValue($array, $key, $default) {
		return $array [$key] ?? $default;
	}

	/**
	 * Save a php array to the disk.
	 *
	 * @param array $array The array to save
	 * @param string $filename The path of the file to save in
	 * @return int
	 */
	public static function save($array, $filename) {
		$content = "<?php\nreturn " . self::asPhpArray ( $array, "array", 1, true ) . ";";
		return UFileSystem::save ( $filename, $content );
	}

	public static function remove($array, $search) {
		if (\is_array ( $search )) {
			foreach ( $search as $val ) {
				$array = self::removeOne ( $array, $val );
			}
		} else {
			$array = self::removeOne ( $array, $search );
		}
		return \array_values ( $array );
	}

	/**
	 * Removes from array by key
	 *
	 * @param array $array
	 * @param int|string $key
	 * @return array
	 */
	public static function removeByKey($array, $key) {
		if (isset ( $array [$key] )) {
			unset ( $array [$key] );
		}
		return $array;
	}

	public static function removeRecursive(&$array, $key) {
		foreach ( $array as &$item ) {
			if (\array_key_exists ( $key, $item )) {
				unset ( $item [$key] );
			} elseif (\is_array ( $item )) {
				self::removeRecursive ( $item, $key );
			}
		}
	}

	public static function removeByKeys($array, $keys) {
		$assocKeys = [ ];
		foreach ( $keys as $key ) {
			$assocKeys [$key] = true;
		}
		return \array_diff_key ( $array, $assocKeys );
	}

	public static function removeOne($array, $search) {
		if (($key = \array_search ( $search, $array )) !== false) {
			unset ( $array [$key] );
		}
		return $array;
	}

	public static function update(&$array, $search, $newValue) {
		if (($key = \array_search ( $search, $array )) !== false) {
			$array [$key] = $newValue;
		}
		return $array;
	}

	/**
	 *
	 * @param array $array
	 * @return boolean
	 */
	public static function doubleBackSlashes(&$array) {
		return \array_walk ( $array, function (&$value) {
			$value = UString::doubleBackSlashes ( $value );
		} );
	}

	public static function iSearch($needle, $haystack, $strict = false) {
		return \array_search ( strtolower ( $needle ), array_map ( 'strtolower', $haystack ), $strict );
	}

	public static function iRemove($array, $search) {
		if (\is_array ( $search )) {
			foreach ( $search as $val ) {
				$array = self::iRemoveOne ( $array, $val );
			}
		} else {
			$array = self::iRemoveOne ( $array, $search );
		}
		return \array_values ( $array );
	}

	public static function iRemoveOne($array, $search) {
		if (($key = self::iSearch ( $search, $array )) !== false) {
			unset ( $array [$key] );
		}
		return $array;
	}

	public static function implodeAsso($array, $glue, $op = '=', $quoteKey = '"', $quoteValue = '"') {
		$res = [ ];
		foreach ( $array as $k => $v ) {
			if(\is_string($k)){
				$res [] = "{$quoteKey}{$k}{$quoteKey}{$op}{$quoteValue}{$v}{$quoteValue}";
			}else{
				$res [] = "{$quoteKey}{$v}{$quoteKey}";
			}
		}
		return \implode ( $glue, $res );
	}

	public static function toJSON($array) {
		$result = [ ];
		foreach ( $array as $k => $v ) {
			if (\is_array ( $v )) {
				$v = self::toJSON ( $v );
			} elseif ($v != null && UString::startswith ( $v, 'js:' )) {
				$v = \substr ( $v, 3 );
			} else {
				$v = '"' . $v . '"';
			}
			$result [] = '"' . $k . '": ' . $v;
		}
		return '{' . \implode ( ',', $result ) . '}';
	}
}
