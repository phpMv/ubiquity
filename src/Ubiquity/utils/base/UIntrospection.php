<?php

namespace Ubiquity\utils\base;

use Ubiquity\domains\DDDManager;

/**
 * Ubiquity\utils\base$UIntrospection
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.10
 *
 */
class UIntrospection {

	public static function getClassCode($classname) {
		$r = new \ReflectionClass($classname);
		$lines = \file($r->getFileName());
		return $lines;
	}

	public static function getFileName($classname) {
		$r = new \ReflectionClass($classname);
		return $r->getFileName();
	}

	public static function getMethodAtLine($class, $line) {
		$r = new \ReflectionClass($class);
		$methods = $r->getMethods();
		foreach ($methods as $method) {
			if ($method->getStartLine() <= $line && $line <= $method->getEndLine()) {
				return $method;
			}
		}
		return null;
	}

	public static function getLoadedViews(\ReflectionMethod $r, $lines) {
		$result = [];
		$code = self::getMethodCode($r, $lines);
		\preg_match_all('@(?:.*?)\$this\-\>loadView\([\'\"](.+?)[\'\"](?:.*?)@s', $code, $matches);
		if (isset($matches[1]) && \sizeof($matches[1]) > 0) {
			$result = array_merge($result, $matches[1]);
		}
		\preg_match_all('@(?:.*?)\$this\-\>jquery\-\>renderView\([\'\"](.+?)[\'\"](?:.*?)@s', $code, $matches);
		if (isset($matches[1])) {
			$result = array_merge($result, $matches[1]);
		}
		if (\strpos($code, '$this->loadDefaultView') !== false || strpos($code, '$this->jquery->renderDefaultView') !== false) {
			$result[] = DDDManager::getViewNamespace() . $r->getDeclaringClass()->getShortName() . '/' . $r->getName() . '.html';
		}
		return $result;
	}

	public static function getMethodCode(\ReflectionMethod $r, $lines) {
		$str = '';
		$count = \count($lines);
		$sLine = $r->getStartLine() - 1;
		$eLine = $r->getEndLine();
		if ($sLine == $eLine)
			return $lines[$sLine];
		$min = \min($eLine, $count);
		for ($l = $sLine; $l < $min; $l++) {
			$str .= $lines[$l];
		}
		return $str;
	}

	public static function getMethodEffectiveParameters($code, $methodName) {
		$tokens = \token_get_all($code);
		$parenthesis = 0;
		$result = [];
		$status = '';
		$current = '';
		foreach ($tokens as $tokenArray) {
			if (\is_array($tokenArray)) {
				if ($tokenArray[0] === T_STRING && $tokenArray[1] === $methodName) {
					$status = 'find';
				} elseif ($status === 'open') {
					$current .= $tokenArray[1];
				}
			} elseif (\is_string($tokenArray)) {
				if ($tokenArray === '(' && $status === 'find') {
					$status = 'open';
					$current = '';
					$parenthesis++;
				} elseif ($status === 'open') {
					if ($tokenArray === '(') {
						$current .= $tokenArray;
						$parenthesis++;
					} elseif ($tokenArray === ',' && $parenthesis === 1) {
						$result[] = \trim($current);
						$current = '';
					} elseif ($tokenArray === ')') {
						$parenthesis--;
						if ($parenthesis === 0) {
							if ($current != '') {
								$result[] = \trim($current);
							}
							return $result;
						}
						$current .= $tokenArray;
					} else {
						$current .= $tokenArray;
					}
				}
			}
		}
		return $result;
	}

	public static function implementsMethod($object, $methodName, $baseDeclaringClass) {
		$m = new \ReflectionMethod($object, $methodName);
		return $m->getDeclaringClass()->getName() !== $baseDeclaringClass;
	}

	public static function closure_dump(\Closure $c) {
		$str = 'function (';
		$r = new \ReflectionFunction($c);
		$params = array();
		foreach ($r->getParameters() as $p) {
			$s = '';
			$type = $p->getType();
			$isArray = $type && $type->getName() === 'array';
			if ($isArray) {
				$s .= 'array ';
			} else if ($type) {
				$class = !$type->isBuiltin() ? new \ReflectionClass($type->getName()) : null;
				if ($class != null) {
					$s .= $class . ' ';
				}
			}
			if ($p->isPassedByReference()) {
				$s .= '&';
			}
			$s .= '$' . $p->name;
			if ($p->isOptional()) {
				$s .= ' = ' . \var_export($p->getDefaultValue(), true);
			}
			$params[] = $s;
		}
		$str .= \implode(', ', $params);
		$str .= ')';
		$lines = file($r->getFileName());
		$sLine = $r->getStartLine();
		$eLine = $r->getEndLine();
		if ($sLine < count($lines)) {
			if ($eLine === $sLine) {
				$match = \strstr($lines[$sLine - 1], "function");
				$str .= \strstr(\strstr($match, "{"), "}", true) . "}";
			} else {
				$str .= \strrchr($lines[$sLine - 1], "{");
				for ($l = $sLine; $l < $eLine - 1; $l++) {
					$str .= $lines[$l];
				}
				$str .= \strstr($lines[$eLine - 1], "}", true) . "}";
			}
		}
		$vars = $r->getStaticVariables();

		foreach ($vars as $k => $v) {
			$str = \str_replace('$' . $k, \var_export($v, true), $str);
		}
		return $str;
	}

	public static function getChildClasses($baseClass) {
		$children = [];
		foreach (\get_declared_classes() as $class) {
			$rClass = new \ReflectionClass($class);
			if ($rClass->isSubclassOf($baseClass)) {
				$children[] = $class;
			}
		}
		return $children;
	}
}
