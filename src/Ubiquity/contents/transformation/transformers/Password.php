<?php

namespace Ubiquity\contents\transformation\transformers;

use Ubiquity\utils\base\UString;
use Ubiquity\contents\transformation\TransformerViewInterface;

/**
 * Mask a password.
 * Ubiquity\contents\transformation\transformers$Password
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since 2.1.1
 */
class Password implements TransformerViewInterface {

	public static function toView($value) {
		if ($value != null)
			return UString::mask ( $value );
	}
}
