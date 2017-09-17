<?php

namespace micro\annotations\router;

use micro\annotations\BaseAnnotation;

/**
 * @usage('method'=>true,'class'=>true,'multiple'=>true, 'inherited'=>true)
 */
class RouteAnnotation extends BaseAnnotation {
	public $path;
	public $methods;
	public $name;
	public $cache;
	public $duration;

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		if (isset($properties[0])) {
			$this->path=$properties[0];
			unset($properties[0]);
		} else
			$this->path="";
		parent::initAnnotation($properties);
	}
}