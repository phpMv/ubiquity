<?php

namespace Ubiquity\annotations;

/**
 * Annotation Table.
 * usages :
 * - table("tableName")
 * - table("name"=>"tableName")
 *
 * @author jc
 * @version 1.0.2
 * @usage('class'=>true, 'inherited'=>true)
 */
class TableAnnotation extends BaseAnnotation {
	public $name;

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		if (isset ( $properties [0] )) {
			$this->name = $properties [0];
			unset ( $properties [0] );
		} else if (isset ( $properties ['name'] )) {
			$this->name = $properties ['name'];
		} else {
			throw new \Exception ( 'Table annotation must have a name' );
		}
	}
}
