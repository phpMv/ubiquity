<?php

namespace Ubiquity\cache\parser;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\utils\base\UString;
use Ubiquity\annotations\router\RouteAnnotation;
use Ubiquity\cache\ClassUtils;

class ControllerParser {
	private $controllerClass;
	private $mainRouteClass;
	private $routesMethods=[ ];
	private $rest=false;
	private static $excludeds=[ "__construct","isValid","initialize","finalize","onInvalidControl","loadView","forward","redirectToRoute" ];

	public function parse($controllerClass) {
		$automated=false;
		$inherited=false;
		$this->controllerClass=$controllerClass;
		$reflect=new \ReflectionClass($controllerClass);
		if (!$reflect->isAbstract() && $reflect->isSubclassOf("Ubiquity\controllers\Controller")) {
			$instance=new $controllerClass();
			try{
			$annotsClass=Reflexion::getAnnotationClass($controllerClass, "@route");
			$restAnnotsClass=Reflexion::getAnnotationClass($controllerClass, "@rest");
			}catch (\Exception $e){
				
			}
			$this->rest=\sizeof($restAnnotsClass) > 0;
			if (\sizeof($annotsClass) > 0) {
				$this->mainRouteClass=$annotsClass[0];
				$inherited=$this->mainRouteClass->inherited;
				$automated=$this->mainRouteClass->automated;
			}
			$methods=Reflexion::getMethods($instance, \ReflectionMethod::IS_PUBLIC);
			foreach ( $methods as $method ) {
				if ($method->getDeclaringClass()->getName() === $controllerClass || $inherited) {
					try{
						$annots=Reflexion::getAnnotationsMethod($controllerClass, $method->name, "@route");
						if ($annots !== false) {
							foreach ( $annots as $annot ) {
								if (UString::isNull($annot->path)) {
									$newAnnot=$this->generateRouteAnnotationFromMethod($method);
									$annot->path=$newAnnot[0]->path;
								}
							}
							$this->routesMethods[$method->name]=[ "annotations" => $annots,"method" => $method ];
						} else {
							if ($automated) {
								if ($method->class !== 'Ubiquity\\controllers\\Controller' && \array_search($method->name, self::$excludeds) === false && !UString::startswith($method->name, "_"))
									$this->routesMethods[$method->name]=[ "annotations" => $this->generateRouteAnnotationFromMethod($method),"method" => $method ];
							}
						}
					}catch(\Exception $e){}
				}
			}
		}
	}

	private function generateRouteAnnotationFromMethod(\ReflectionMethod $method) {
		$annot=new RouteAnnotation();
		$annot->path=self::getPathFromMethod($method);
		return [ $annot ];
	}

	private static function getPathFromMethod(\ReflectionMethod $method) {
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

	private static function cleanpath($prefix, $path="") {
		if (!UString::endswith($prefix, "/"))
			$prefix=$prefix . "/";
		if ($path !== "" && UString::startswith($path, "/"))
			$path=\substr($path, 1);
		$path=$prefix . $path;
		if (!UString::endswith($path, "/") && !UString::endswith($path, '(.*?)') && !UString::endswith($path, "(index/)?"))
			$path=$path . "/";
		return $path;
	}

	public function asArray() {
		$result=[ ];
		$prefix="";
		$httpMethods=false;
		if ($this->mainRouteClass) {
			if (isset($this->mainRouteClass->path))
				$prefix=$this->mainRouteClass->path;
			if (isset($this->mainRouteClass->methods)) {
				$httpMethods=$this->mainRouteClass->methods;
				if ($httpMethods !== null) {
					if (\is_string($httpMethods))
						$httpMethods=[ $httpMethods ];
				}
			}
		}
		foreach ( $this->routesMethods as $method => $arrayAnnotsMethod ) {
			$routeAnnotations=$arrayAnnotsMethod["annotations"];

			foreach ( $routeAnnotations as $routeAnnotation ) {
				$params=[ "path" => $routeAnnotation->path,"methods" => $routeAnnotation->methods,"name" => $routeAnnotation->name,"cache" => $routeAnnotation->cache,"duration" => $routeAnnotation->duration,"requirements" => $routeAnnotation->requirements ];
				self::parseRouteArray($result, $this->controllerClass, $params, $arrayAnnotsMethod["method"], $method, $prefix, $httpMethods);
			}
		}
		return $result;
	}

	public static function parseRouteArray(&$result, $controllerClass, $routeArray, \ReflectionMethod $method, $methodName, $prefix="", $httpMethods=NULL) {
		if (!isset($routeArray["path"])) {
			$routeArray["path"]=self::getPathFromMethod($method);
		}
		$pathParameters=self::addParamsPath($routeArray["path"], $method, $routeArray["requirements"]);
		$name=$routeArray["name"];
		if (!isset($name)) {
			$name=UString::cleanAttribute(ClassUtils::getClassSimpleName($controllerClass) . "_" . $methodName);
		}
		$cache=$routeArray["cache"];
		$duration=$routeArray["duration"];
		$path=$pathParameters["path"];
		$parameters=$pathParameters["parameters"];
		$path=self::cleanpath($prefix, $path);
		if (isset($routeArray["methods"]) && \is_array($routeArray["methods"])) {
			self::createRouteMethod($result, $controllerClass, $path, $routeArray["methods"], $methodName, $parameters, $name, $cache, $duration);
		} elseif (\is_array($httpMethods)) {
			self::createRouteMethod($result, $controllerClass, $path, $httpMethods, $methodName, $parameters, $name, $cache, $duration);
		} else {
			$result[$path]=[ "controller" => $controllerClass,"action" => $methodName,"parameters" => $parameters,"name" => $name,"cache" => $cache,"duration" => $duration];
		}
	}

	public static function addParamsPath($path, \ReflectionMethod $method, $requirements) {
		$parameters=[ ];
		$hasOptional=false;
		preg_match_all('@\{(\.\.\.|\~)?(.+?)\}@s', $path, $matches);
		if (isset($matches[2]) && \sizeof($matches[2]) > 0) {
			$path=\preg_quote($path);
			$params=Reflexion::getMethodParameters($method);
			$index=0;
			foreach ( $matches[2] as $paramMatch ) {
				$find=\array_search($paramMatch, $params);
				if ($find !== false) {
					$requirement='.+?';
					if (isset($requirements[$paramMatch])) {
						$requirement=$requirements[$paramMatch];
					}
					self::scanParam($parameters, $hasOptional, $matches, $index, $paramMatch, $find, $path, $requirement);
				} else {
					throw new \Exception("{$paramMatch} is not a parameter of the method " . $method->name);
				}
				$index++;
			}
		}
		if ($hasOptional)
			$path.="/(.*?)";
		return [ "path" => $path,"parameters" => $parameters ];
	}

	private static function scanParam(&$parameters, &$hasOptional, $matches, $index, $paramMatch, $find, &$path, $requirement) {
		if (isset($matches[1][$index])) {
			if ($matches[1][$index] === "...") {
				$parameters[]="*";
				$path=\str_replace("\{\.\.\." . $paramMatch . "\}", "(.*?)", $path);
			} elseif ($matches[1][$index] === "~") {
				$parameters[]="~" . $find;
				$path=\str_replace("\{~" . $paramMatch . "\}", "", $path);
				$hasOptional=true;
			} else {
				$parameters[]=$find;
				$path=\str_replace("\{" . $paramMatch . "\}", "({$requirement})", $path);
			}
		} else {
			$parameters[]=$find;
			$path=\str_replace("\{" . $paramMatch . "\}", "({$requirement})", $path);
		}
	}

	private static function createRouteMethod(&$result, $controllerClass, $path, $httpMethods, $method, $parameters, $name, $cache, $duration) {
		foreach ( $httpMethods as $httpMethod ) {
			$result[$path][$httpMethod]=[ "controller" => $controllerClass,"action" => $method,"parameters" => $parameters,"name" => $name,"cache" => $cache,"duration" => $duration ];
		}
	}

	public function isRest() {
		return $this->rest;
	}
}
