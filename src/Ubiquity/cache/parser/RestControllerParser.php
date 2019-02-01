<?php

namespace Ubiquity\cache\parser;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\ClassUtils;

class RestControllerParser {
	private $controllerClass;
	private $resource;
	private $route;
	private $rest;
	private $authorizationMethods;

	public function __construct() {
		$this->rest=false;
		$this->authorizationMethods=[ ];
	}

	public function parse($controllerClass, $config) {
		$this->controllerClass=$controllerClass;
		$reflect=new \ReflectionClass($controllerClass);
		if (!$reflect->isAbstract() && $reflect->isSubclassOf("Ubiquity\\controllers\\rest\\RestController")) {
			$restAnnotsClass=Reflexion::getAnnotationClass($controllerClass, "@rest");
			if (\sizeof($restAnnotsClass) > 0) {
				$routeAnnotsClass=Reflexion::getAnnotationClass($controllerClass, "@route");
				if (\sizeof($routeAnnotsClass) > 0) {
					$this->route=$routeAnnotsClass[0]->path;
				}
				$this->resource=$this->_getResourceName($config, $restAnnotsClass[0]->resource);
				$this->rest=true;
				$methods=Reflexion::getMethods($controllerClass, \ReflectionMethod::IS_PUBLIC);
				foreach ( $methods as $method ) {
					$annots=Reflexion::getAnnotationsMethod($controllerClass, $method->name, "@authorization");
					if ($annots !== false) {
						$this->authorizationMethods[]=$method->name;
					}
				}
			}
		}
	}

	private function _getResourceName($config, $name) {
		$modelsNS=$config["mvcNS"]["models"];
		return ClassUtils::getClassNameWithNS($modelsNS, $name);
	}

	public function asArray() {
		return [ $this->controllerClass => [ "resource" => $this->resource,"authorizations" => $this->authorizationMethods,"route" => $this->route ] ];
	}

	public function isRest() {
		return $this->rest;
	}
}
