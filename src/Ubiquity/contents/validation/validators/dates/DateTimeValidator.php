<?php

namespace Ubiquity\contents\validation\validators\dates;


class DateTimeValidator extends AbstractDateTimeValidator {
	public function __construct(){
		$this->ref='Y-m-d H:i:s';
		$this->message="This value is not a valid datetime according to the format `{ref}`";
	}
}

