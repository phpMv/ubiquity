<?php

namespace Ubiquity\views\engine;

use Ubiquity\utils\base\UString;

/**
 * Ubiquity abstract TemplateGenerator class.
 *
 * Ubiquity\views\engine$TemplateGenerator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class TemplateGenerator {

	public function parseFromTwig(string $code){
		return $code;
	}

}
