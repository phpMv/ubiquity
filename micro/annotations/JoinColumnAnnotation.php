<?php

namespace micro\annotations;

/**
 * Annotation JoinColumn
 * @author jc
 * @version 1.0.0.1
 */
class JoinColumnAnnotation extends ColumnAnnotation {
	public $className;
	public $referencedColumnName;
}
