<?php

namespace micro\annotations;

/**
 * Annotation JoinTable
 * @author jc
 * @version 1.0.0.1
 */
class JoinTableAnnotation extends BaseAnnotation {
	public $name;
	public $joinColumns;
	public $inverseJoinColumns;
}
