<?php

namespace Ubiquity\annotations;

/**
 * Annotation Table.
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
		} else {
			throw new \Exception ( 'Table annotation must have a name' );
		}
	}
}
