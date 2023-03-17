<?php

namespace Ubiquity\contents\transformation\transformers;

use Ubiquity\contents\transformation\TransformerViewInterface;

/**
 * Hide a password.
 * Ubiquity\contents\transformation\transformers$HidePassword
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since 2.5.3
 */
class HidePassword implements TransformerViewInterface {

	public static function toView($value) {
		return '';
	}
}
