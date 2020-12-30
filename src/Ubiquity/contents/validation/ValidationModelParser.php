<?php

namespace Ubiquity\contents\validation;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\utils\base\UArray;

/**
 * @author jc
 *
 */
class ValidationModelParser {

	protected $validators=[];

	public function parse($modelClass) {
		$instance=new $modelClass();
		$properties=Reflexion::getProperties($instance);
		foreach ( $properties as $property ) {
			$propName=$property->getName();
			$annots=Reflexion::getAnnotationsMember($modelClass, $propName, 'validator');
			if(\count($annots)>0){
				$this->validators[$propName]=[];
				foreach ($annots as $annotation){
					$this->validators[$propName][]=$annotation->getPropertiesAndValues();
				}
			}
		}

	}

	public function getValidators(){
		return $this->validators;
	}

	public function __toString() {
		return "return " . UArray::asPhpArray($this->validators, "array") . ";";
	}
}

