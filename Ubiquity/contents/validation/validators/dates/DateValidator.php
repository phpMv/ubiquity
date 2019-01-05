<?php

namespace Ubiquity\contents\validation\validators\dates;


class DateValidator extends AbstractDateTimeValidator {
	public function __construct(){
		$this->ref='Y-m-d';
		$this->message="This value is not a valid date according to the format `{ref}`";
	}
}

