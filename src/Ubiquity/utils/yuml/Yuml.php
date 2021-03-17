<?php

namespace Ubiquity\utils\yuml;

class Yuml {
	public static $classMask='[{classContent}]';
	public static $classSeparator='|';
	public static $memberSeparator=';';
	public static $parameterSeparator='‚';
	public static $parameterTypeSeparator=':';
	public static $groupeSeparator=",";
	public static $propertyMask='{access}{primary}{name}{type}';
	public static $methodMask='{access}{name}({parameters}){type}';
	public static $public='+';
	public static $protected='#';
	public static $private='-';
	public static $primary='«pk» ';

	public static function parseMask($element,$variable,$value) {
		$result=preg_replace('/(\{)'.$variable.'(\})/sim', $value, $element);
		return $result;
	}

	public static function parseMaskArray($element,$variables,$values) {
		$result=$element;
		$countValues=\count($values);
		$maxVariables=\count($variables)-1;
		for($i=0;$i<$countValues;$i++){
			$j=\min($maxVariables,$i);
			$result=self::parseMask($result, $variables[$j], $values[$i]);
		}
		return $result;
	}

	public static function getNamesInMask($mask){
		\preg_match_all('@\{(.*?)\}@sim', $mask,$matches);
		if(isset($matches[1]))
			return $matches[1];
		return [];
	}

	private static function replaceMaskValues($mask,$values){
		return self::parseMaskArray($mask, self::getNamesInMask($mask), $values);
	}

	public static function setPropertyVariables($values){
		return self::replaceMaskValues(self::$propertyMask, $values);
	}

	public static function setMethodVariables($values){
		return self::replaceMaskValues(self::$methodMask, $values);
	}

	public static function setClassContent($content){
		return self::parseMask(self::$classMask, 'classContent', $content);
	}
}
