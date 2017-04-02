<?php
namespace micro\annotations;

/**
 * Annotation Column
 * @author jc
 * @version 1.0.0.1
 * @package annotations
 */
class ColumnAnnotation extends BaseAnnotation{
	public $name;
	public $nullable=false;

	public function checkConstraints($target){
		/*if(is_null($this->name))
			throw new \Exception("L'attribut name est obligatoire pour une annotation de type Column");
			*/
	}
}
