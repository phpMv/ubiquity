<?php

namespace Ubiquity\cache\parser;

use Ubiquity\utils\base\UString;
use Ubiquity\orm\parser\Reflexion;

/**
 * Ubiquity\cache\parser$ControllerParserPathTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
trait ControllerParserPathTrait {

	protected static function getPathFromMethod(\ReflectionMethod $method) {
		$methodName = $method->getName ();
		if ($methodName === "index") {
			$pathParts = [ "(index/)?" ];
		} else {
			$pathParts = [ $methodName ];
		}
		$parameters = $method->getParameters ();
		foreach ( $parameters as $parameter ) {
			if ($parameter->isVariadic ()) {
				$pathParts [] = '{...' . $parameter->getName () . '}';
				return "/" . \implode ( "/", $pathParts );
			}
			if (! $parameter->isOptional ()) {
				$pathParts [] = '{' . $parameter->getName () . '}';
			} else {
				$pathParts [\sizeof ( $pathParts ) - 1] .= '{~' . $parameter->getName () . '}';
			}
		}
		return "/" . \implode ( "/", $pathParts );
	}

	public static function parseMethodPath(\ReflectionFunctionAbstract $method, $path) {
		if (! isset ( $path ) || $path === '')
			return;
		$parameters = $method->getParameters ();
		foreach ( $parameters as $parameter ) {
			$name = $parameter->getName ();
			if ($parameter->isVariadic ()) {
				$path = str_replace ( '{' . $name . '}', '{...' . $name . '}', $path );
			} elseif ($parameter->isOptional ()) {
				$path = str_replace ( '{' . $name . '}', '{~' . $name . '}', $path );
			}
		}
		return $path;
	}

	public static function cleanpath($prefix, $path = "") {
		$path = str_replace ( "//", "/", $path );
		if ($prefix !== "" && ! UString::startswith ( $prefix, "/" )) {
			$prefix = "/" . $prefix;
		}
		if (! UString::endswith ( $prefix, "/" )) {
			$prefix = $prefix . "/";
		}
		if ($path !== "" && UString::startswith ( $path, "/" )) {
			$path = \substr ( $path, 1 );
		}
		$path = $prefix . $path;
		if (! UString::endswith ( $path, "/" ) && ! UString::endswith ( $path, '(.*?)' ) && ! UString::endswith ( $path, "(index/)?" )) {
			$path = $path . "/";
		}
		return $path;
	}

	// TODO check
	public static function addParamsPath($path, \ReflectionFunctionAbstract $method, $requirements) {
		$parameters = [ ];
		$hasOptional = false;
		preg_match_all ( '@\{(\.\.\.|\~)?(.+?)\}@s', $path, $matches );
		if (isset ( $matches [2] ) && \sizeof ( $matches [2] ) > 0) {
			$path = \preg_quote ( $path );
			$params = Reflexion::getMethodParameters ( $method );
			$index = 0;
			foreach ( $matches [2] as $paramMatch ) {
				$find = \array_search ( $paramMatch, $params );
				if ($find !== false) {
					$requirement = '.+?';
					if (isset ( $requirements [$paramMatch] )) {
						$requirement = $requirements [$paramMatch];
					}
					self::scanParam ( $parameters, $hasOptional, $matches, $index, $paramMatch, $find, $path, $requirement );
				} else {
					throw new \Exception ( "{$paramMatch} is not a parameter of the method " . $method->name );
				}
				$index ++;
			}
		}
		if ($hasOptional)
			$path .= "/(.*?)";
		return [ "path" => $path,"parameters" => $parameters ];
	}

	public static function scanParam(&$parameters, &$hasOptional, $matches, $index, $paramMatch, $find, &$path, $requirement) {
		$toReplace = true;
		if (isset ( $matches [1] [$index] )) {
			if ($matches [1] [$index] === "...") {
				$parameters [] = "*";
				$path = \str_replace ( "\{\.\.\." . $paramMatch . "\}", "(.*?)", $path );
				$toReplace = false;
			} elseif ($matches [1] [$index] === "~") {
				$parameters [] = "~" . $find;
				$path = \str_replace ( "\{~" . $paramMatch . "\}", "", $path );
				$hasOptional = true;
				$toReplace = false;
			}
		}
		if ($toReplace) {
			$parameters [] = $find;
			$path = \str_replace ( "\{" . $paramMatch . "\}", "({$requirement})", $path );
		}
	}
}

