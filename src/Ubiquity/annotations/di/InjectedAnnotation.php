<?php

namespace Ubiquity\annotations\di;

use Ubiquity\annotations\BaseAnnotation;

/**
 * Annotation for dependency injection.
 * usages :
 * - injected
 * - injected(name)
 * - injected(name,code)
 *
 * @author jc
 * @version 1.0.0
 * @since Ubiquity 2.1.0
 */
class InjectedAnnotation extends BaseAnnotation {
	public $name;
	public $code;

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		if (isset ( $properties [0] )) {
			$this->name = $properties [0];
			unset ( $properties [0] );
			if (isset ( $properties [1] )) {
				$this->code = $properties [1];
				unset ( $properties [1] );
			}
		}
		parent::initAnnotation ( $properties );
	}
}
