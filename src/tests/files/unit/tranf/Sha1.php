<?php

namespace transf;

use Ubiquity\contents\transformation\TransformerViewInterface;

class Sha1 implements TransformerViewInterface {

	public static function toView($value) {
		if ($value != null)
			return sha1 ( $value );
	}
}