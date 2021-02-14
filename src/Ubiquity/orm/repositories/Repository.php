<?php

namespace Ubiquity\orm\repositories;

/**
 * Ubiquity\orm\repositories$Repository
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 */
class Repository extends AbstractRepository {
	protected string $model;

	public function __construct(string $model) {
		$this->model = $model;
	}

	protected function getModel(): string {
		return $this->model;
	}
}

