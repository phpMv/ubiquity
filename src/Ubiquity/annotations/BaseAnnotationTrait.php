<?php
namespace Ubiquity\annotations;

use Ubiquity\utils\base\UArray;

/**
 * Ubiquity\annotations$BaseAnnotationTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.1
 *
 */
trait BaseAnnotationTrait {
	abstract protected function getDefaultParameters(): array;

	public function getProperties() {
		$reflect = new \ReflectionClass ( $this );
		$props = $reflect->getProperties ();
		return $props;
	}

	public function getPropertiesAndValues($props = NULL) {
		$ret = [];
		$defaultParameters=$this->getDefaultParameters();
		if (\is_null ( $props ))
			$props = $this->getProperties ();
			foreach ( $props as $prop ) {
				$prop->setAccessible ( true );
				$name=$prop->getName();
				$v = $prop->getValue ( $this );
				if ($v !== null && $v !== '' && isset ( $v )) {
					if(!isset($defaultParameters[$name]) || $defaultParameters[$name]!==$v){
						$ret [$name] = $v;
					}
				}
			}
			return $ret;
	}
	
	public function isSameAs($annot):bool{
		return \get_class($this)===\get_class($annot);
	}
}

