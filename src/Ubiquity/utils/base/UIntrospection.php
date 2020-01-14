<?php

namespace Ubiquity\utils\base;

/**
 * Ubiquity\utils\base$UIntrospection
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.7
 *
 */
class UIntrospection {

	public static function getClassCode($classname) {
		$r = new \ReflectionClass ( $classname );
		$lines = file ( $r->getFileName () );
		return $lines;
	}

	public static function getFileName($classname) {
		$r = new \ReflectionClass ( $classname );
		return $r->getFileName ();
	}

	public static function getLoadedViews(\ReflectionMethod $r, $lines) {
		$result = [ ];
		$code = self::getMethodCode ( $r, $lines );
		\preg_match_all ( '@(?:.*?)\$this\-\>loadView\([\'\"](.+?)[\'\"](?:.*?)@s', $code, $matches );
		if (isset ( $matches [1] ) && \sizeof ( $matches [1] ) > 0) {
			$result = array_merge ( $result, $matches [1] );
		}
		\preg_match_all ( '@(?:.*?)\$this\-\>jquery\-\>renderView\([\'\"](.+?)[\'\"](?:.*?)@s', $code, $matches );
		if (isset ( $matches [1] )) {
			$result = array_merge ( $result, $matches [1] );
		}
		if (\strpos ( $code, '$this->loadDefaultView' ) !== false || strpos ( $code, '$this->jquery->renderDefaultView' ) !== false) {
			$result [] = $r->getDeclaringClass ()->getShortName () . '/' . $r->getName () . '.html';
		}
		return $result;
	}

	public static function getMethodCode(\ReflectionMethod $r, $lines) {
		$str = "";
		$count = \sizeof ( $lines );
		$sLine = $r->getStartLine ();
		$eLine = $r->getEndLine ();
		if ($sLine == $eLine)
			return $lines [$sLine];
		$min = \min ( $eLine, $count );
		for($l = $sLine; $l < $min; $l ++) {
			$str .= $lines [$l];
		}
		return $str;
	}

	public static function implementsMethod($object, $methodName, $baseDeclaringClass) {
		$m = new \ReflectionMethod ( $object, $methodName );
		return $m->getDeclaringClass ()->getName () !== $baseDeclaringClass;
	}

	public static function closure_dump(\Closure $c) {
		$str = 'function (';
		$r = new \ReflectionFunction ( $c );
		$params = array ();
		foreach ( $r->getParameters () as $p ) {
			$s = '';
			if ($p->isArray ()) {
				$s .= 'array ';
			} else if ($p->getClass ()) {
				$s .= $p->getClass ()->name . ' ';
			}
			if ($p->isPassedByReference ()) {
				$s .= '&';
			}
			$s .= '$' . $p->name;
			if ($p->isOptional ()) {
				$s .= ' = ' . \var_export ( $p->getDefaultValue (), TRUE );
			}
			$params [] = $s;
		}
		$str .= \implode ( ', ', $params );
		$str .= ')';
		$lines = file ( $r->getFileName () );
		$sLine = $r->getStartLine ();
		$eLine = $r->getEndLine ();
		if ($eLine === $sLine) {
			$match = \strstr ( $lines [$sLine - 1], "function" );
			$str .= \strstr ( \strstr ( $match, "{" ), "}", true ) . "}";
		} else {
			$str .= \strrchr ( $lines [$sLine - 1], "{" );
			for($l = $sLine; $l < $eLine - 1; $l ++) {
				$str .= $lines [$l];
			}
			$str .= \strstr ( $lines [$eLine - 1], "}", true ) . "}";
		}
		$vars = $r->getStaticVariables ();
		foreach ( $vars as $k => $v ) {
			$str = \str_replace ( '$' . $k, \var_export ( $v, true ), $str );
		}
		return $str;
	}

	public static function getChildClasses($baseClass) {
		$children = [ ];
		foreach ( \get_declared_classes () as $class ) {
			$rClass = new \ReflectionClass ( $class );
			if ($rClass->isSubclassOf ( $baseClass )) {
				$children [] = $class;
			}
		}
		return $children;
	}
}
