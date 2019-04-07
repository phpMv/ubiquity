<?php

namespace Ubiquity\contents\transformation\transformers;

use Ubiquity\contents\transformation\TransformerViewInterface;

/**
 * Make a string uppercase.
 * Ubiquity\contents\transformation\transformers$UpperCase
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since 2.1.1
 */
class UpperCase implements TransformerViewInterface {

	public static function toView($value) {
		if ($value != null)
			return strtoupper ( $value );
	}
}
