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
		$ret = [];
		$defaultParameters=$this->getDefaultParameters();
		if (\is_null ( $props ))
			$props = $this->getProperties ( $this );
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

	protected function getDefaultParameters():array{
		$r=new \ReflectionMethod(get_class($this),'__construct');
		$result=[];
		foreach ($r->getParameters() as $param){
			if($param->isOptional()){
				$result[$param->getName()]=$param->getDefaultValue();
			}
		}
		return $result;
	}
}

