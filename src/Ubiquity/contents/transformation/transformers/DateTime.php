<?php

namespace Ubiquity\contents\transformation\transformers;

use Ubiquity\contents\transformation\TransformerInterface;
use Ubiquity\contents\transformation\TransformerViewInterface;

/**
 * Transform a mysql date to a php DateTime.
 * Ubiquity\contents\transformation\transformers$DateTime
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since 2.1.1
 */
class DateTime implements TransformerInterface,TransformerViewInterface {

	public static function transform($value) {
		if ($value != null)
			return new \DateTime ( $value );
	}

	public static function reverse($value) {
		if ($value instanceof \DateTime) {
			return $value->format ( 'Y-m-d H:i:s' );
		}
	}

	public static function toView($value) {
		if($value instanceof \DateTime)
			return self::reverse($value);
		return $value;
	}
}
