<?php

namespace Ubiquity\contents\transformation\transformers;

use Ubiquity\contents\transformation\TransformerInterface;

/**
 * Make a string first character uppercase.
 * Ubiquity\contents\transformation\transformers$FirstUpperCase
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since 2.1.1
 */
class FirstUpperCase implements TransformerInterface {

	public static function transform($value) {
		if ($value != null)
			return ucfirst ( $value );
	}

	public static function reverse($value) {
		return lcfirst ( $value );
	}

	public static function toView($value) {
		return $value;
	}
}
