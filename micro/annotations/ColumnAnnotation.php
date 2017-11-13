<?php

namespace micro\annotations;

/**
 * Annotation Column
 * @author jc
 * @version 1.0.0.2
 * @package annotations
 */
class ColumnAnnotation extends BaseAnnotation {
	public $name;
	public $nullable=false;
	public $dbType;
}
