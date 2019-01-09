<?php

namespace Ubiquity\contents\validation;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\utils\base\UArray;
use Ubiquity\annotations\ValidatorAnnotation;

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
			$annots=Reflexion::getAnnotationsMember($modelClass, $propName, "@validator");
			if(sizeof($annots)>0){
				$this->validators[$propName]=[];
				foreach ($annots as $annotation){
					if($annotation instanceof ValidatorAnnotation){
						$this->validators[$propName][]=$annotation->getPropertiesAndValues();
					}
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

