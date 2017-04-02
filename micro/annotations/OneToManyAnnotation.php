<?php
namespace micro\annotations;

/**
 * Annotation OneToMany
 * @author jc
 * @version 1.0.0.2
 * @package annotations
 */
class OneToManyAnnotation extends BaseAnnotation{
	public $mappedBy;
	public $fetch;
	public $className;
	public function checkConstraints($target){
		/*if(is_null($this->mappedBy))
			throw new \Exception("L'attribut mappedBy est obligatoire pour une annotation de type OneToMany");
		if(is_null($this->className))
			throw new \Exception("L'attribut className est obligatoire pour une annotation de type OneToMany");
			*/
	}

}
