<?php

namespace Ubiquity\utils\base;

class UIntrospection {

	public static function getClassCode($classname) {
		$r=new \ReflectionClass($classname);
		$lines=file($r->getFileName());
		return $lines;
	}

	public static function getFileName($classname) {
		$r=new \ReflectionClass($classname);
		return $r->getFileName();
	}

	public static function getLoadedViews(\ReflectionMethod $r, $lines) {
		$matches=[ ];
		$code=self::getMethodCode($r, $lines);
		\preg_match_all('@(?:.*?)\$this\-\>loadView\([\'\"](.+?)[\'\"](?:.*?)@s', $code, $matches);
		if (isset($matches[1])) {
			return $matches[1];
		}
		return [ ];
	}

	public static function getMethodCode(\ReflectionMethod $r, $lines) {
		$str="";
		$count=\sizeof($lines);
		$sLine=$r->getStartLine();
		$eLine=$r->getEndLine();
		if ($sLine == $eLine)
			return $lines[$sLine];
		$min=\min($eLine, $count);
		for($l=$sLine; $l < $min; $l++) {
			$str.=$lines[$l];
		}
		return $str;
	}

	public static function closure_dump(\Closure $c) {
		$str='function (';
		$r=new \ReflectionFunction($c);
		$params=array ();
		foreach ( $r->getParameters() as $p ) {
			$s='';
			if ($p->isArray()) {
				$s.='array ';
			} else if ($p->getClass()) {
				$s.=$p->getClass()->name . ' ';
			}
			if ($p->isPassedByReference()) {
				$s.='&';
			}
			$s.='$' . $p->name;
			if ($p->isOptional()) {
				$s.=' = ' . \var_export($p->getDefaultValue(), TRUE);
			}
			$params[]=$s;
		}
		$str.=\implode(', ', $params);
		$str.=')';
		$lines=file($r->getFileName());
		$sLine=$r->getStartLine();
		$eLine=$r->getEndLine();
		if($eLine===$sLine){
			$str.=strstr(strstr($lines[$sLine-1],"{"),"}",true)."}";
		}else{
			$str.=strstr($lines[$sLine-1],"{");
			for($l=$sLine; $l < $eLine-1; $l++) {
				$str.=$lines[$l];
			}
			$str.=strstr($lines[$eLine-1],"}",true)."}";
		}
		return $str;
	}

	public static function getChildClasses($baseClass){
		$children  = [];
		foreach(\get_declared_classes() as $class){
			echo $class."<br>";
			$rClass=new \ReflectionClass($class);
			if($rClass->isSubclassOf($baseClass)){
				$children[] = $class;
			}
		}
		return $children;
	}
}
