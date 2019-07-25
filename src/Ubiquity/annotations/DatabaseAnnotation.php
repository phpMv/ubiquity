<?php

namespace Ubiquity\annotations;

/**
 * Annotation Database.
 * usages :
 * - table("database")
 * - table("name"=>"database")
 *
 * @author jc
 * @version 1.0.0
 * @usage('class'=>true, 'inherited'=>true)
 */
class DatabaseAnnotation extends BaseAnnotation {
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
			throw new \Exception ( 'Database annotation must have a name' );
		}
	}
}
