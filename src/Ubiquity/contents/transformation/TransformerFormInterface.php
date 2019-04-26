<?php

/**
 * Transformers managment
 */
namespace Ubiquity\contents\transformation;

/**
 * Transform an instance for being used in a form.
 * Ubiquity\contents\transformation$TransformerFormInterface
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since Ubiquity 2.1.1
 *
 */
interface TransformerFormInterface {

	/**
	 * Transforms normalized data to form data
	 *
	 * @param string $value
	 */
	public static function toForm($value);
}
