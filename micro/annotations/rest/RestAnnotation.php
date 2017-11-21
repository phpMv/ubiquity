<?php
namespace micro\annotations\rest;

use micro\annotations\BaseAnnotation;

/**
 * Annotation Rest
 * @author jc
 * @version 1.0.0.1
 * @usage('class'=>true, 'inherited'=>true)
 */
class RestAnnotation extends BaseAnnotation {
	public $resource;
}
