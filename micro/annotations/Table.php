<?php

/**
 * Annotation Table
 * @author jc
 * @version 1.0.0.1
 * @package annotations
 * @Target("class")
 */

class Table extends \Annotation{
	public $name;
	public function checkConstraints($target){
		if(is_null($this->name))
			throw new \Exception("L'attribut name est obligatoire pour une annotation de type Table");
	}
}