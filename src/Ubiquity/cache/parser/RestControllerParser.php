<?php

namespace Ubiquity\cache\parser;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\rest\RestBaseController;

/**
 * Ubiquity\cache\parser$RestControllerParser
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class RestControllerParser {
	private $controllerClass;
	private $resource;
	private $route;
	private $rest;
	private $authorizationMethods;

	public function __construct() {
		$this->rest = false;
		$this->authorizationMethods = [ ];
	}

	public function parse($controllerClass, $config) {
		$this->controllerClass = $controllerClass;
		$reflect = new \ReflectionClass ( $controllerClass );
		if (! $reflect->isAbstract () && $reflect->isSubclassOf ( RestBaseController::class )) {
			$restAnnotsClass = Reflexion::getAnnotationClass ( $controllerClass, "@rest" );
			if (\sizeof ( $restAnnotsClass ) > 0) {
				$routeAnnotsClass = Reflexion::getAnnotationClass ( $controllerClass, "@route" );
				if (\sizeof ( $routeAnnotsClass ) > 0) {
					$this->route = $routeAnnotsClass [0]->path;
				}
				$this->resource = $this->_getResourceName ( $config, $restAnnotsClass [0]->resource );
				$this->rest = true;
				$methods = Reflexion::getMethods ( $controllerClass, \ReflectionMethod::IS_PUBLIC );
				foreach ( $methods as $method ) {
					$annots = Reflexion::getAnnotationsMethod ( $controllerClass, $method->name, "@authorization" );
					if ($annots !== false) {
						$this->authorizationMethods [] = $method->name;
					}
				}
			}
		}
	}

	private function _getResourceName($config, $name) {
		if ($name != null) {
			$modelsNS = $config ["mvcNS"] ["models"];
			return ClassUtils::getClassNameWithNS ( $modelsNS, $name );
		}
		return '';
	}

	public function asArray() {
		return [ $this->controllerClass => [ "resource" => $this->resource,"authorizations" => $this->authorizationMethods,"route" => $this->route ] ];
	}

	public function isRest() {
		return $this->rest;
	}
}
