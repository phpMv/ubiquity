<?php

namespace Ubiquity\cache\parser;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UString;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\exceptions\ParserException;

/**
 * Ubiquity\cache\parser$ControllerParserPathTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.1
 *
 */
trait ControllerParserPathTrait {
	protected static $mainParams;
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
				$pathParts [\count ( $pathParts ) - 1] .= '{~' . $parameter->getName () . '}';
			}
		}
		return "/" . \implode ( "/", $pathParts );
	}
	
	private static function checkParams(\ReflectionFunctionAbstract $method,$actualParams){
		foreach ( $method->getParameters () as $param ) {
			if(!$param->isOptional() && \array_search($param->name,$actualParams)===false){
				throw new ParserException(sprintf('The parameter %s is not present in the route path although it is mandatory.',$param->name));
			}
		}
	}
	
	private static function checkParamsTypesForRequirement(\ReflectionFunctionAbstract $method){
		$requirements=[];
		foreach ( $method->getParameters () as $param ) {
			if($param->hasType()){
				$type=$param->getType();
				if($type instanceof \ReflectionNamedType){
					switch ($type->getName()){
						case 'int':
							$requirements[$param->getName()]='\d+';
							break;
						case 'bool':
							$requirements[$param->getName()]='[0-1]{1}';
							break;
						case 'float':
							$requirements[$param->getName()]='[+-]?([0-9]*[.])?[0-9]+';
							break;
					}
				}
			}
		}
		return $requirements;
	}
	
	public static function parseMethodPath(\ReflectionFunctionAbstract $method, $path) {
		if (! isset ( $path ) || $path === '') {
			return;
		}
		$parameters = $method->getParameters ();
		foreach ( $parameters as $parameter ) {
			$name = $parameter->getName ();
			if ($parameter->isVariadic ()) {
				$path = \str_replace ( '{' . $name . '}', '{...' . $name . '}', $path );
			} elseif ($parameter->isOptional ()) {
				$path = \str_replace ( '{' . $name . '}', '{~' . $name . '}', $path );
			}
		}
		return $path;
	}
	
	public static function cleanpath($prefix, $path = "", &$isRoot=false) {
		$path = \str_replace ( '//', '/', $path );
		if ($prefix !== '' && ! UString::startswith ( $prefix, '/' )) {
			$prefix = '/' . $prefix;
		}
		if (! UString::endswith ( $prefix, '/' )) {
			$prefix = $prefix . '/';
		}
		if ($path !== '' && UString::startswith ( $path, '/' )) {
			$path = \substr ( $path, 1 );
		}
		if(UString::startswith($path,'#/')){
			$path=\substr($path,1);
			$isRoot=true;
		}else {
			$path = $prefix . $path;
		}
		if (! UString::endswith ( $path, '/' ) && ! UString::endswith ( $path, '(.*?)' ) && ! UString::endswith ( $path, '(index/)?' )) {
			$path = $path . '/';
		}
		return \str_replace ( '//', '/', $path );
	}
	
	public static function addParamsPath($path, \ReflectionFunctionAbstract $method, $requirements) {
		$parameters = [ ];
		$hasOptional = false;
		\preg_match_all ( '@\{(\.\.\.|\~)?(.+?)\}@s', $path, $matches );
		self::checkParams($method,$matches[2]??[]);
		if (isset ( $matches [2] ) && \count ( $matches [2] ) > 0) {
			$path = \preg_quote ( $path );
			$params = Reflexion::getMethodParameters ( $method );
			$typeRequirements=self::checkParamsTypesForRequirement($method);
			$index = 0;
			foreach ( $matches [2] as $paramMatch ) {
				$find = \array_search ( $paramMatch, $params );
				if ($find !== false) {
					unset($params[$find]);
					$requirement = '.+?';
					if (isset ( $requirements [$paramMatch] )) {
						$requirement = $requirements [$paramMatch];
					}elseif (isset($typeRequirements[$paramMatch])){
						$requirement = $typeRequirements [$paramMatch];
					}
					self::scanParam ( $parameters, $hasOptional, $matches, $index, $paramMatch, $find, $path, $requirement );
				} else {
					throw new ParserException ( "{$paramMatch} is not a parameter of the method " . $method->name );
				}
				$index ++;
			}
		}
		if ($hasOptional) {
			$path .= '/(.*?)';
		}
		$path=\str_replace('\\#','#',$path);
		return [ 'path' => $path,'parameters' => $parameters ];
	}
	
	public static function scanParam(&$parameters, &$hasOptional, $matches, $index, $paramMatch, $find, &$path, $requirement) {
		$toReplace = true;
		if (isset ( $matches [1] [$index] )) {
			if ($matches [1] [$index] === '...') {
				$parameters [] = '*';
				$path = \str_replace ( '\{\.\.\.' . $paramMatch . '\}', '(.*?)', $path );
				$toReplace = false;
			} elseif ($matches [1] [$index] === '~') {
				$parameters [] = '~' . $find;
				$path = \str_replace ( '\{~' . $paramMatch . '\}', '', $path );
				$hasOptional = true;
				$toReplace = false;
			}
		}
		if ($toReplace) {
			$parameters [] = $find;
			$path = \str_replace ( '\{' . $paramMatch . '\}', "({$requirement})", $path );
		}
	}
	
	protected static function parseMainPath(string $path,string $controllerClass): string{
		\preg_match_all ( '@\{(.+?)\}@s', $path, $matches );
		self::$mainParams=[];
		if (isset ( $matches [1] ) && \count ( $matches [1] ) > 0) {
			foreach ( $matches [1] as $paramMatch ) {
				if(\substr($paramMatch, -2) === '()'){
					$method=\substr($paramMatch,0,\strlen($paramMatch)-2);
					if(\method_exists($controllerClass,$method)){
						self::$mainParams[]=$method;
						$path = \str_replace('{' . $paramMatch . '}', '(.+?)', $path);
					}else{
						throw new ParserException("Method $method does not exist on $controllerClass");
					}
				}else{
					if(\property_exists($controllerClass,$paramMatch)){
						$rProp=new \ReflectionProperty($controllerClass,$paramMatch);
						if($rProp->isPublic()){
							$path = \str_replace('{' . $paramMatch . '}', '(.+?)', $path);
							self::$mainParams[]=$paramMatch;
						}else{
							throw new ParserException("Property $paramMatch must be public $controllerClass");
						}
					}else{
						throw new ParserException("Property $paramMatch does not exist on $controllerClass");
					}
				}
				
			}
		}
		return $path;
	}
}

