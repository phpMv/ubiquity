<?php

namespace Ubiquity\annotations;

/**
 * Annotation Transformer.
 * usage :
 * - transformer("name"=>"transformerName")
 *
 * @author jc
 * @version 1.0.1
 * @usage('property'=>true, 'inherited'=>true)
 * @since Ubiquity 2.1.1
 */
class TransformerAnnotation extends BaseAnnotation {
	public $name;

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		if (isset ( $properties [0] )) {
			$this->name = $properties [0];
			unset ( $properties [0] );
		} else {
			throw new \Exception ( 'Transformer annotation must have a name' );
		}
	}
}
