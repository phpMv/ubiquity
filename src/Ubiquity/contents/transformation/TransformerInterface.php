<?php

namespace Ubiquity\contents\transformation;

/**
 * Transform an instance.
 * Ubiquity\contents\transformation$TransformerInterface
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since Ubiquity 2.1.1
 *
 */
interface TransformerInterface {

	/**
	 * Transforms model data to normalized data
	 *
	 * @param mixed $value
	 */
	public static function transform($value);

	/**
	 * Reverse data transforming to model data
	 *
	 * @param mixed $value
	 */
	public static function reverse($value);

}
