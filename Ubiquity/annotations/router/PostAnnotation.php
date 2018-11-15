<?php

namespace Ubiquity\annotations\router;

class PostAnnotation extends RouteAnnotation{
	public function initAnnotation(array $properties) {
		parent::initAnnotation($properties);
		$this->methods=["post"];
	}
}

