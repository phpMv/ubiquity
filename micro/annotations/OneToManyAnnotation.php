<?php

namespace micro\annotations;

/**
 * Annotation OneToMany
 * @author jc
 * @version 1.0.0.2
 */
class OneToManyAnnotation extends BaseAnnotation {
	public $mappedBy;
	public $fetch;
	public $className;
}
