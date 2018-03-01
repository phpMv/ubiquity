<?php

namespace Ubiquity\annotations\router;

use Ubiquity\annotations\BaseAnnotation;

/**
 * @usage('method'=>true,'class'=>true,'multiple'=>true, 'inherited'=>true)
 */
class RouteAnnotation extends BaseAnnotation {
	public $path;
	public $methods;
	public $name;
	public $cache;
	public $duration;
	public $inherited;
	public $automated;
	public $requirements;

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		$this->inherited=false;
		$this->automated=false;
		$this->requirements=[];
		if (isset($properties[0])) {
			$this->path=$properties[0];
			unset($properties[0]);
		} else
			$this->path="";
		parent::initAnnotation($properties);
	}
}