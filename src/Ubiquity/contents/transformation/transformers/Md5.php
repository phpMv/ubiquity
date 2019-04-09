<?php

namespace Ubiquity\contents\transformation\transformers;

use Ubiquity\contents\transformation\TransformerViewInterface;

class Md5 implements TransformerViewInterface {

	public static function toView($value) {
		if ($value != null)
			return md5 ( $value );
	}
}