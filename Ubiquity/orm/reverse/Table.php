<?php

namespace Ubiquity\orm\reverse;

use Ubiquity\orm\OrmUtils;

class Table {
	private $model;

	public function __construct($model){
		$this->model=$model;
	}

	public function generateSQL(){
		$metas=OrmUtils::getModelMetadata($this->model);
		$table=OrmUtils::getTableName($this->model);
		$fieldnames=OrmUtils::getAnnotationInfo($this->model, "#fieldNames");
		$primaryKeys=OrmUtils::getKeyFields($this->model);
		$serializables=OrmUtils::getSerializableFields($this->model);
	}
}
