<?php

namespace Ubiquity\annotations\router;

class GetAnnotation extends RouteAnnotation{
	public function initAnnotation(array $properties) {
		parent::initAnnotation($properties);
		$this->methods=["get"];
	}
}

