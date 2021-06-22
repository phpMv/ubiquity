<?php

namespace Ubiquity\contents\transformation\transformers;

use Ubiquity\contents\transformation\TransformerViewInterface;

class Boolean implements TransformerViewInterface {

	public static function toView($value) {
		if ($value != null)
			return \filter_var ( $value, FILTER_VALIDATE_BOOLEAN ) === true;
	}
}