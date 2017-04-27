<?php

namespace micro\annotations;

/**
 * Annotation ManyToMany
 * @author jc
 * @version 1.0.0.2
 */
class ManyToManyAnnotation extends BaseAnnotation {
	public $targetEntity;
	public $inversedBy;
	public $mappedBy;
}
