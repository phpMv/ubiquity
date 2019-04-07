<?php

namespace Ubiquity\contents\transformation;

/**
 * Transform an instance for being displayed in a view.
 * Ubiquity\contents\transformation$TransformerViewInterface
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since Ubiquity 2.1.1
 *
 */
interface TransformerViewInterface {

	/**
	 * Transforms normalized data to view data
	 *
	 * @param string $value
	 */
	public static function toView($value);
}
