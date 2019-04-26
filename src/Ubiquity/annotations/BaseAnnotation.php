<?php

/**
 * Annotations
 */
namespace Ubiquity\annotations;

use Ubiquity\utils\base\UArray;
use mindplay\annotations\Annotation;

/**
 * Base class for annotations.
 *
 * @usage('property'=>true, 'inherited'=>true)
 */
class BaseAnnotation extends Annotation {

	public function getProperties() {
		$reflect = new \ReflectionClass ( $this );
		$props = $reflect->getProperties ();
		return $props;
	}

	public function getPropertiesAndValues($props = NULL) {
		$ret = array ();
		if (is_null ( $props ))
			$props = $this->getProperties ( $this );
		foreach ( $props as $prop ) {
			$prop->setAccessible ( true );
			$v = $prop->getValue ( $this );
			if ($v !== null && $v !== "" && isset ( $v )) {
				$ret [$prop->getName ()] = $v;
			}
		}
		return $ret;
	}

	public function asPhpArray() {
		$fields = $this->getPropertiesAndValues ();
		return UArray::asPhpArray ( $fields );
	}

	public function initAnnotation(array $properties) {
		foreach ( $properties as $name => $value ) {
			if (is_array ( $this->$name )) {
				if (is_array ( $value )) {
					foreach ( $value as $k => $v ) {
						$this->$name [$k] = $v;
					}
				} else {
					$this->$name [] = $value;
				}
			} else {
				$this->$name = $value;
			}
		}
	}

	protected function asAnnotation() {
		return $this->asPhpArray ();
	}

	public function __toString() {
		$extsStr = $this->asAnnotation ();
		$className = get_class ( $this );
		$annotName = \substr ( $className, \strlen ( "Ubiquity\annotations\\" ) );
		$annotName = \substr ( $annotName, 0, \strlen ( $annotName ) - \strlen ( "Annotation" ) );
		return "@" . \lcfirst ( $annotName ) . $extsStr;
	}
}
