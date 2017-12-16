<?php

namespace Ubiquity\utils;

class Introspection {

	public static function getClassCode($classname){
		$r = new \ReflectionClass($classname);
		$lines = file($r->getFileName());
		return $lines;
	}

	public static function getLoadedViews(\ReflectionMethod $r,$lines){
		$matches=[];
		$code=self::getMethodCode($r,$lines);
		\preg_match_all('@(?:.*?)\$this\-\>loadView\([\'\"](.+?)[\'\"](?:.*?)@s', $code,$matches);
		if (isset($matches[1])) {
			return $matches[1];
		}
		return [];
	}

	public static function getMethodCode(\ReflectionMethod $r,$lines){
		$str="";
		$count=\sizeof($lines);
		$sLine=$r->getStartLine();$eLine=$r->getEndLine();
		if($sLine==$eLine)
			return $lines[$sLine];
		for($l = $sLine; $l < min($eLine,$count); $l++) {
			$str .= $lines[$l];
		}
		return $str;
	}

	public static function closure_dump(\Closure $c) {
		$str = 'function (';
		$r = new \ReflectionFunction($c);
		$params = array();
		foreach($r->getParameters() as $p) {
			$s = '';
			if($p->isArray()) {
				$s .= 'array ';
			} else if($p->getClass()) {
				$s .= $p->getClass()->name . ' ';
			}
			if($p->isPassedByReference()){
				$s .= '&';
			}
			$s .= '$' . $p->name;
			if($p->isOptional()) {
				$s .= ' = ' . \var_export($p->getDefaultValue(), TRUE);
			}
			$params []= $s;
		}
		$str .= \implode(', ', $params);
		$str .= '){' . PHP_EOL;
		$lines = file($r->getFileName());
		$sLine=$r->getStartLine();$eLine=$r->getEndLine();
		for($l = $sLine; $l < $eLine; $l++) {
			$str .= $lines[$l];
		}
		return $str;
	}
}
