<?php

namespace Ubiquity\annotations;

/**
 * Annotation ManyToMany.
 * usages :
 * - manyToMany("targetEntity"=>"classname")
 * - manyToMany("targetEntity"=>"classname","inversedBy"=>"memberName")
 * - manyToMany("targetEntity"=>"classname","inversedBy"=>"memberName","mappedBy"=>"memberName")
 *
 * @author jc
 * @version 1.0.2
 */
class ManyToManyAnnotation extends BaseAnnotation {
	public $targetEntity;
	public $inversedBy;
	public $mappedBy;
}
