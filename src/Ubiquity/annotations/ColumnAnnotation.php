<?php

namespace Ubiquity\annotations;

/**
 * Annotation Column.
 * usages :
 * - column("name"=>"columnName")
 * - column("name"=>"columnName","nullable"=>true)
 * - column("name"=>"columnName","dbType"=>"typeInDb")
 *
 * @author jc
 * @version 1.0.2
 */
class ColumnAnnotation extends BaseAnnotation {
	public $name;
	public $nullable = false;
	public $dbType;
}
