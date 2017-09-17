<?php

namespace micro\cache;

use micro\orm\parser\Reflexion;
use micro\utils\StrUtils;

class ControllerParser {
	private $controllerClass;
	private $mainRouteClass;
	private $routesMethods=[ ];

	public function parse($controllerClass) {
		$this->controllerClass=$controllerClass;
		$reflect=new \ReflectionClass($controllerClass);
		if (!$reflect->isAbstract()) {
			$instance=new $controllerClass();
			$annotsClass=Reflexion::getAnnotationClass($controllerClass, "@route");
			if (\sizeof($annotsClass) > 0)
				$this->mainRouteClass=$annotsClass[0];
			$methods=Reflexion::getMethods($instance, \ReflectionMethod::IS_PUBLIC);
			foreach ( $methods as $method ) {
				$annots=Reflexion::getAnnotationsMethod($controllerClass, $method->name, "@route");
				if ($annots !== false)
					$this->routesMethods[$method->name]=[ "annotations" => $annots,"method" => $method ];
			}
		}
	}

	private static function cleanpath($prefix, $path="") {
		if (!StrUtils::endswith($prefix, "/"))
			$prefix=$prefix . "/";
		if ($path !== "" && StrUtils::startswith($path, "/"))
			$path=\substr($path, 1);
		$path=$prefix . $path;
		if (StrUtils::endswith($path, "/"))
			$path=\substr($path, 0, \strlen($path) - 1);
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
				$params=[ "path" => $routeAnnotation->path,"methods" => $routeAnnotation->methods,"name" => $routeAnnotation->name,"cache" => $routeAnnotation->cache,"duration" => $routeAnnotation->duration ];
				self::parseRouteArray($result, $this->controllerClass,
						$params,
						$arrayAnnotsMethod["method"], $method, $prefix, $httpMethods);
			}
		}
		return $result;
	}

	public static function parseRouteArray(&$result, $controllerClass, $routeArray, \ReflectionMethod $method, $methodName, $prefix="", $httpMethods=NULL) {
		if (isset($routeArray["path"])) {
			$pathParameters=self::addParamsPath($routeArray["path"], $method);
			$name=$routeArray["name"];
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
				$result[$path]=[ "controller" => $controllerClass,"action" => $methodName,"parameters" => $parameters,"name" => $name,"cache" => $cache,"duration" => $duration ];
			}
		}
	}

	public static function addParamsPath($path, \ReflectionMethod $method) {
		$parameters=[ ];
		preg_match_all('@\{(.+?)\}@s', $path, $matches);
		if (isset($matches[1]) && \sizeof($matches[1]) > 0) {
			$path=\preg_quote($path);
			$params=Reflexion::getMethodParameters($method);
			foreach ( $matches[1] as $paramMatch ) {
				$find=\array_search($paramMatch, $params);
				if ($find !== false) {
					$parameters[]=$find;
					$path=\str_replace("\{" . $paramMatch . "\}", "(.+?)", $path);
				} else {
					throw new \Exception("{$paramMatch} is not a parameter of the method " . $method->name);
				}
			}
		}
		return [ "path" => $path,"parameters" => $parameters ];
	}

	private static function createRouteMethod(&$result, $controllerClass, $path, $httpMethods, $method, $parameters, $name, $cache, $duration) {
		foreach ( $httpMethods as $httpMethod ) {
			$result[$path][$httpMethod]=[ "controller" => $controllerClass,"action" => $method,"parameters" => $parameters,"name" => $name,"cache" => $cache,"duration" => $duration ];
		}
	}
}
