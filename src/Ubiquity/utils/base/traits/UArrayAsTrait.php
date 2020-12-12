<?php

namespace Ubiquity\utils\base\traits;

use Ubiquity\utils\base\UIntrospection;
use Ubiquity\utils\base\UString;

/**
 * Ubiquity\utils\base\traits$UArrayAsTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.5
 *
 */
trait UArrayAsTrait {
	private static function parseValue($v, $prefix = '', $depth = 1, $format = false) {
		if (\is_numeric ( $v )) {
			$result = $v;
		} elseif ($v !== '' && UString::isBooleanStr ( $v )) {
			$result = UString::getBooleanStr ( $v );
		} elseif (\is_array ( $v )) {
			$result = self::asPhpArray ( $v, $prefix, $depth + 1, $format );
		} elseif (\is_string ( $v ) && (UString::startswith ( \trim ( $v ), '$config' ) || UString::startswith ( \trim ( $v ), 'function' ) || UString::startswith ( \trim ( $v ), 'array(' ))) {
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
	public static function asPhpArray($array, $prefix = '', $depth = 1, $format = false) {
		$exts = array ();
		$extsStr = '';
		$tab = '';
		$nl = '';
		if ($format) {
			$tab = \str_repeat ( "\t", $depth );
			$nl = PHP_EOL;
		}
		foreach ( $array as $k => $v ) {
			if (\is_string ( $k )) {
				$exts [] = "\"" . UString::doubleBackSlashes ( $k ) . "\"=>" . self::parseValue ( $v, 'array', $depth + 1, $format );
			} else {
				$exts [] = self::parseValue ( $v, $prefix, $depth + 1, $format );
			}
		}
		if ($prefix !== '') {
			$extsStr = '()';
		}
		if (\sizeof ( $exts ) > 0) {
			$extsStr = "({$nl}{$tab}" . \implode ( ",{$nl}{$tab}", $exts ) . "{$nl}{$tab})";
		}
		return $prefix . $extsStr;
	}

	public static function asPhpClass($array, $name, $namespace = '', $format = false) {
		$tab = '';
		$nl = '';
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
}

