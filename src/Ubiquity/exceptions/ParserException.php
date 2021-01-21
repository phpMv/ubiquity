<?php

namespace Ubiquity\exceptions;

/**
 * Exceptions for Parsers
 *
 * @author jc
 *
 */
class ParserException extends UbiquityException {

	public function __construct($message = null, $code = null, $previous = null) {
		parent::__construct ( $message, $code, $previous );
	}
}
