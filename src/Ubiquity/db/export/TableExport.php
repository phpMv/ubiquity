<?php

namespace Ubiquity\db\export;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;

class TableExport extends DataExport{
	protected $model;
	protected $metas;
	public function __construct($model,$batchSize=20){
		parent::__construct($batchSize);
		$this->model=$model;
		$this->batchSize=$batchSize;
	}

	public function exports(DbExport $dbExport,$condition=""){
		$table=OrmUtils::getTableName($this->model);
		$this->metas=OrmUtils::getModelMetadata($this->model);
		$manyToManys=[];
		if(isset($this->metas["#manyToMany"]))
			$manyToManys=$this->metas["#manyToMany"];
		$this->scanManyToManys($dbExport, $manyToManys);
		$fields=\array_diff($this->metas["#fieldNames"],$this->metas["#notSerializable"]);
		$datas=DAO::getAll($this->model,$condition);
		return $this->generateInsert($table, $fields, $datas);
	}

	protected function scanManyToManys(DbExport $dbExport,$manyToManys){
		foreach ($manyToManys as $member=>$manyToMany){
			if(isset($this->metas["#joinTable"][$member])){
				$annotJoinTable=$this->metas["#joinTable"][$member];
				$dbExport->addManyToMany($annotJoinTable["name"], ["member"=>$member,"class"=>$this->model]);
			}
		}
	}
}
