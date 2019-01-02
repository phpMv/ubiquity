<?php

namespace Ubiquity\cache\parser;

use Ubiquity\utils\base\UString;

trait ControllerParserPathTrait {
	
	protected  static function getPathFromMethod(\ReflectionMethod $method) {
		$methodName=$method->getName();
		if ($methodName === "index") {
			$pathParts=[ "(index/)?" ];
		} else {
			$pathParts=[ $methodName ];
		}
		$parameters=$method->getParameters();
		foreach ( $parameters as $parameter ) {
			if ($parameter->isVariadic()) {
				$pathParts[]='{...' . $parameter->getName() . '}';
				return "/" . \implode("/", $pathParts);
			}
			if (!$parameter->isOptional()) {
				$pathParts[]='{' . $parameter->getName() . '}';
			} else {
				$pathParts[\sizeof($pathParts) - 1].='{~' . $parameter->getName() . '}';
			}
		}
		return "/" . \implode("/", $pathParts);
	}
	
	protected  static function parseMethodPath(\ReflectionMethod $method,$path){
		if(!isset($path) || $path==='')
			return;
			$parameters=$method->getParameters();
			foreach ( $parameters as $parameter ) {
				$name=$parameter->getName();
				if ($parameter->isVariadic()) {
					$path=str_replace('{'.$name.'}', '{...' . $name . '}',$path);
				}elseif ($parameter->isOptional()) {
					$path=str_replace('{'.$name.'}', '{~' . $name . '}',$path);
				}
			}
			return $path;
	}
	
	protected  static function cleanpath($prefix, $path="") {
		$path=str_replace("//", "/", $path);
		if($prefix!=="" && !UString::startswith($prefix, "/")){
			$prefix="/".$prefix;
		}
		if (!UString::endswith($prefix, "/")){
			$prefix=$prefix . "/";
		}
		if ($path !== "" && UString::startswith($path, "/")){
			$path=\substr($path, 1);
		}
		$path=$prefix . $path;
		if (!UString::endswith($path, "/") && !UString::endswith($path, '(.*?)') && !UString::endswith($path, "(index/)?")){
			$path=$path . "/";
		}
		return $path;
	}
}

