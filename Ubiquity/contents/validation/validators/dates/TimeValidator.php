<?php

namespace Ubiquity\contents\validation\validators\dates;


class TimeValidator extends AbstractDateTimeValidator {
	public function __construct(){
		$this->ref='H:i:s';
		$this->message="This value is not a valid time according to the format `{ref}`";
	}
}

