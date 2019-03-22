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
 * @version 1.0.0
 * @since Ubiquity 2.1.0
 *
 */
class DiControllerParser {
	protected $injections = [ ];

	public function parse($controllerClass, $config) {
		$instance = new $controllerClass ();
		$properties = Reflexion::getProperties ( $instance );
		foreach ( $properties as $property ) {
			$propName = $property->getName ();
			$annot = Reflexion::getAnnotationMember ( $controllerClass, $propName, "@injected" );
			if ($annot !== false) {
				$name = $annot->name;
				$this->injections [$propName] = $this->getInjection ( $name ?? $propName, $config,$controllerClass, $annot->code??null );
			} else {
				$annot = Reflexion::getAnnotationMember ( $controllerClass, $propName, "@autowired" );
				if ($annot !== false) {
					$type = Reflexion::getPropertyType ( $controllerClass, $propName );
					if ($type !== false) {
						$this->injections [$propName] = "function(\$controller){return new " . $type . "();}";
					}else {
						throw new DiException ( sprintf ( '%s property has no type and cannot be autowired!', $propName ) );
					}
				}
			}
		}
		$this->scanGlobalDi ( $config ['di'] ?? [ ], $controllerClass );
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

	protected function getInjection($name, $config, $controller,$code = null) {
		if ($code != null) {
			return "function(\$controller){return " . $code . ";}";
		}
		if (isset ( $config ["di"] )) {
			$di = $config ['di'];
			if ($name != null){
				$classname = ClassUtils::getClassSimpleName ( $controller );
				if(isset ( $di [$name] )) {
					return $di[$name];
				}elseif(isset ( $di [$classname.'.'.$name] )){
					return $di[$classname.'.'.$name];
				}elseif(isset ( $di ['*.'.$name] )){
					return $di['*.'.$name];
				}else {
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
	 * @return multitype:
	 */
	public function getInjections() {
		return $this->injections;
	}
}

