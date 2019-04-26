<?php

/**
 * Router annotations
 */
namespace Ubiquity\annotations\router;

/**
 * Defines a route with the `get` method
 * Ubiquity\annotations\router$GetAnnotation
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class GetAnnotation extends RouteAnnotation {

	public function initAnnotation(array $properties) {
		parent::initAnnotation ( $properties );
		$this->methods = [ "get" ];
	}
}

