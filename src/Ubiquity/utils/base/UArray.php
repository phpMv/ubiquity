<?php

namespace Ubiquity\utils\base;

/**
 * Array utilities.
 * Ubiquity\utils\base$UArray
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
class UArray {

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
		$values = array_values ( $array );
		if ($pos < \sizeof ( $values ))
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

	public static function asPhpArray($array, $prefix = "", $depth = 1, $format = false) {
		$exts = array ();
		$extsStr = "";
		$tab = "";
		$nl = "";
		if ($format) {
			$tab = \str_repeat ( "\t", $depth );
			$nl = PHP_EOL;
		}
		foreach ( $array as $k => $v ) {
			if (is_string ( $k )) {
				$exts [] = "\"" . UString::doubleBackSlashes ( $k ) . "\"=>" . self::parseValue ( $v, 'array', $depth + 1, $format );
			} else {
				$exts [] = self::parseValue ( $v, $prefix, $depth + 1, $format );
			}
		}
		if (\sizeof ( $exts ) > 0 || $prefix !== "") {
			$extsStr = "(" . \implode ( "," . $nl . $tab, $exts ) . ")";
			if (\sizeof ( $exts ) > 0) {
				$extsStr = "(" . $nl . $tab . \implode ( "," . $nl . $tab, $exts ) . $nl . $tab . ")";
			}
		}
		return $prefix . $extsStr;
	}

	public static function asPhpClass($array, $name, $namespace = '', $format = false) {
		$tab = "";
		$nl = "";
		if ($format) {
			$tab = "\t";
			$nl = PHP_EOL;
		}
		$content = 'public static $value=' . self::asPhpArray ( $array, 'array', 1, true ) . ';';
		if ($namespace != null) {
			$namespace = "namespace {$namespace};{$nl}";
		}
		return "{$namespace}class {$name} {" . $nl . $tab . $content . $nl . $tab . "}";
	}

	public static function asJSON($array) {
		return \json_encode ( $array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );
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

	private static function parseValue($v, $prefix = "", $depth = 1, $format = false) {
		if (\is_numeric ( $v )) {
			$result = $v;
		} elseif ($v !== '' && UString::isBooleanStr ( $v )) {
			$result = UString::getBooleanStr ( $v );
		} elseif (\is_array ( $v )) {
			$result = self::asPhpArray ( $v, $prefix, $depth + 1, $format );
		} elseif (\is_string ( $v ) && (UString::startswith ( trim ( $v ), '$config' ) || UString::startswith ( \trim ( $v ), "function" ) || UString::startswith ( \trim ( $v ), "array(" ))) {
			$result = $v;
		} elseif ($v instanceof \Closure) {
			$result = UIntrospection::closure_dump ( $v );
		} elseif ($v instanceof \DateTime) {
			$result = "\DateTime::createFromFormat('Y-m-d H:i:s','" . $v->format ( 'Y-m-d H:i:s' ) . "')";
		} else {
			$result = UString::doubleBackSlashes ( $v );
			$result = "\"" . \str_replace ( [ '$','"' ], [ '\$','\"' ], $result ) . "\"";
		}
		return $result;
	}

	public static function iSearch($needle, $haystack, $strict = null) {
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
			$res [] = "{$quoteKey}{$k}{$quoteKey}{$op}{$quoteValue}{$v}{$quoteValue}";
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
