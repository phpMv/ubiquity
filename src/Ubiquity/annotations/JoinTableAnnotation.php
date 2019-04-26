<?php

namespace Ubiquity\annotations;

/**
 * Annotation JoinTable.
 * usages :
 * - joinTable("name"=>"tableName")
 * - joinTable("name"=>"tableName","joinColumns"=>"fieldname")
 * - joinTable("name"=>"tableName","joinColumns"=>"fieldname","inverseJoinColumns"=>"fieldname")
 *
 * @author jc
 * @version 1.0.0.1
 */
class JoinTableAnnotation extends BaseAnnotation {
	public $name;
	public $joinColumns;
	public $inverseJoinColumns;
}
