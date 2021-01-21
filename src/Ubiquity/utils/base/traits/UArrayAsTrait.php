<?php

namespace Ubiquity\utils\base\traits;

use Ubiquity\utils\base\UIntrospection;
use Ubiquity\utils\base\UString;

/**
 * Ubiquity\utils\base\traits$UArrayAsTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.6
 *
 */
trait UArrayAsTrait {
	private static function parseValue($v, $depth = 1, $format = false) {
		if (\is_numeric ( $v )) {
			$result = $v;
		} elseif ($v !== '' && UString::isBooleanStr ( $v )) {
			$result = UString::getBooleanStr ( $v );
		} elseif (\is_array ( $v )) {
			$result = self::asPhpArray_ ( $v, $depth + 1, $format );
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
	private static function as_($array,$formatParams=['prefix' => '','before'=>'(','after'=>')'], $valueCallback=null,$depth = 1,$format = false) {
		$exts = [];
		$extsStr = '';
		$tab = '';
		$nl = '';
		if ($format) {
			$tab = \str_repeat ( "\t", $depth );
			$nl = PHP_EOL;
		}
		foreach ( $array as $k => $v ) {
			$v=self::parseValue ( $v, $depth + 1, $format );
			if (\is_string ( $k )) {
				if(isset($valueCallback)){
					$exts []=$valueCallback($k,$v);
				}else{
					$exts [] = "\"" . UString::doubleBackSlashes ( $k ) . "\"=>" . $v;
				}
			} else {
				$exts [] = $v;
			}
		}

		if (\count ( $exts ) > 0) {
			$extsStr = $formatParams['before']."{$nl}{$tab}" . \implode ( ",{$nl}{$tab}", $exts ) . "{$nl}{$tab}".$formatParams['after'];
		}else{
			$extsStr = $formatParams['before'].$formatParams['after'];
		}
		return $formatParams['prefix'] . $extsStr;
	}
	public static function asPhpArray($array, $prefix = '', $depth = 1, $format = false) {
		return self::as_($array,['prefix'=>$prefix,'before'=>'(','after'=>')'],null,$depth,$format);
	}

	public static function asPhpArray_($array, $depth = 1, $format = false) {
		return self::as_($array,['prefix'=>'','before'=>'[','after'=>']'],null,$depth,$format);
	}

	public static function asPhpAttribute($array, $prefix = '', $depth = 1, $format = false) {
		return self::as_($array,['prefix'=>$prefix,'before'=>'(','after'=>')'],function($k,$v){return $k.': '.$v;},$depth,$format);
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

