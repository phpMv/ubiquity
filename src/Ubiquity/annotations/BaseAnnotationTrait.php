<?php
namespace Ubiquity\annotations;

use Ubiquity\utils\base\UArray;

/**
 * Ubiquity\annotations$BaseAnnotationTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
trait BaseAnnotationTrait {
	public function getProperties() {
		$reflect = new \ReflectionClass ( $this );
		$props = $reflect->getProperties ();
		return $props;
	}
	
	public function getPropertiesAndValues($props = NULL) {
		$ret = array ();
		if (\is_null ( $props ))
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
}

