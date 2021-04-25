<?php

namespace Ubiquity\controllers\di;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UString;
use Ubiquity\cache\ClassUtils;
use Ubiquity\exceptions\DiException;

/**
 * Parse the controllers for dependency injections.
 *
 * Ubiquity\controllers\di$DiControllerParser
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 * @since Ubiquity 2.1.0
 *
 */
class DiControllerParser {
	protected $injections = [ ];

	public function parse($controllerClass, $config) {
		$properties = Reflexion::getProperties ( $controllerClass );
		foreach ( $properties as $property ) {
			$propName = $property->getName ();
			$annot = Reflexion::getAnnotationMember ( $controllerClass, $propName, 'injected' );
			if ($annot !== false) {
				$name = $annot->name;
				if ($this->isInjectable ( $controllerClass, $name ?? $propName, false )) {
					$this->injections [$propName] = $this->getInjection ( $name ?? $propName, $config, $controllerClass, $annot->code ?? null);
				}
			} else {
				$annot = Reflexion::getAnnotationMember ( $controllerClass, $propName, 'autowired' );
				if ($annot !== false) {
					$type = Reflexion::getPropertyType ( $controllerClass, $propName );
					if ($type !== false) {
						if ($this->isInjectable ( $controllerClass, $propName, false )) {
							if(\is_string($type)){
								$this->getInjectableAutowired ( $type, $propName );
							}elseif($type instanceof \ReflectionProperty || $type instanceof \ReflectionNamedType){
								$this->getInjectableAutowired ( $type->getName (), $propName );
							}
						}
					} else {
						throw new DiException ( sprintf ( '%s property has no type and cannot be autowired!', $propName ) );
					}
				}
			}
		}
		$this->scanGlobalDi ( $config ['di'] ?? [ ], $controllerClass );
	}

	protected function getInjectableAutowired($type, $propName) {
		$typeR = new \ReflectionClass ( $type );
		if ($typeR->isInstantiable ()) {
			$constructor = $typeR->getConstructor ();
			$nbParams = $constructor == null ? 0 : $typeR->getConstructor ()->getNumberOfRequiredParameters ();
			if ($nbParams == 0) {
				$this->injections [$propName] = "function(){return new " . $type . "();}";
			} elseif ($nbParams == 1) {
				$this->injections [$propName] = "function(\$controller){return new " . $type . "(\$controller);}";
			} else {
				throw new DiException ( sprintf ( 'Service %s constructor has too many mandatory arguments for %s injection!', $type, $propName ) );
			}
		} else {
			$namespace = $typeR->getNamespaceName ();
			$oClass = $namespace . "\\" . ucfirst ( $propName );
			if (class_exists ( $oClass )) {
				if (is_subclass_of ( $oClass, $type )) {
					$this->getInjectableAutowired ( $oClass, $propName );
				} else {
					throw new DiException ( sprintf ( 'Class %s is not a subclass of %s!', $oClass, $type ) );
				}
			} else {
				throw new DiException ( sprintf ( 'Class %s does not exists!', $oClass ) );
			}
		}
	}

	protected function scanGlobalDi($diConfig, $controller) {
		$classname = ClassUtils::getClassSimpleName ( $controller );
		foreach ( $diConfig as $k => $v ) {
			if (UString::startswith ( $k, "*." ) || UString::startswith ( $k, $classname . "." )) {
				$dis = explode ( '.', $k );
				$nkey = end ( $dis );
				if (property_exists ( $controller, $nkey ) === false) {
					$this->injections [$nkey] = $v;
				}
			}
		}
	}

	protected function isInjectable($classname, $member, $silent = true) {
		$prop = new \ReflectionProperty ( $classname, $member );
		if ($prop->isPublic ()) {
			return true;
		}
		$setter = 'set' . ucfirst ( $member );
		if (method_exists ( $classname, $setter )) {
			return true;
		}
		if (! $silent) {
			throw new DiException ( sprintf ( '%s member must be public or have a setter to be injected in the class %s!', $member, $classname ) );
		}
		return false;
	}

	protected function getInjection($name, $config, $controller, $code = null) {
		if ($code != null) {
			return "function(\$controller){return " . $code . ";}";
		}
		if (isset ( $config ["di"] )) {
			$di = $config ['di'];
			if ($name != null) {
				$classname = ClassUtils::getClassSimpleName ( $controller );
				if (isset ( $di [$name] )) {
					return $di [$name];
				} elseif (isset ( $di [$classname . '.' . $name] )) {
					return $di [$classname . '.' . $name];
				} elseif (isset ( $di ['*.' . $name] )) {
					return $di ['*.' . $name];
				} else {
					throw new \Exception ( "key " . $name . " is not present in config di array" );
				}
			}
		} else {
			throw new \Exception ( "key di is not present in config array" );
		}
	}

	public function __toString() {
		return "return " . UArray::asPhpArray ( $this->injections, "array" ) . ";";
	}

	/**
	 *
	 * @return array
	 */
	public function getInjections() {
		return $this->injections;
	}
}

