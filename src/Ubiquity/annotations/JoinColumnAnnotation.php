<?php

namespace Ubiquity\annotations;

/**
 * Annotation JoinColumn.
 * usages :
 * - joinColumn("className"=>"modelClassname")
 * - joinColumn("className"=>"modelClassname","referencedColumnName"=>"columnName")
 *
 * @author jc
 * @version 1.0.0.1
 */
class JoinColumnAnnotation extends ColumnAnnotation {
	public $className;
	public $referencedColumnName;
}
