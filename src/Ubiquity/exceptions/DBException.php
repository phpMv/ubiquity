<?php

namespace Ubiquity\exceptions;

class DBException extends UbiquityException {

	public function __construct($message = null, $code = null, $previous = null) {
		parent::__construct ( $message, $code, $previous );
	}
}
