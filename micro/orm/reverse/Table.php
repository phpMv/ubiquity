<?php

namespace micro\orm\reverse;

use micro\orm\OrmUtils;

class Table {
	private $model;

	public function __construct($model) {
		$this->model=$model;
	}

	public function generateSQL() {
	}
}