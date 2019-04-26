<?php

namespace Ubiquity\annotations;

/**
 * Annotation Yuml.
 * yuml("color"=>"color","note"=>"content")
 *
 * @author jc
 * @version 1.0.1
 * @usage('class'=>true, 'inherited'=>true)
 */
class YumlAnnotation extends BaseAnnotation {
	public $color;
	public $note;
}
