<?php

namespace Ubiquity\orm\reverse;

use Ubiquity\orm\OrmUtils;
use Ubiquity\db\reverse\DbGenerator;

class TableReversor {
	private $model;
	private $fkFieldsToAdd=[];
	private $fkFieldTypesToAdd=[];
	private $metas;

	public function __construct($model=null){
		$this->model=$model;
	}

	public function initFromClass(){
		if(isset($this->model))
			$this->metas=OrmUtils::getModelMetadata($this->model);
	}

	public function init($metas){
		$this->metas=$metas;
	}

	public function generateSQL(DbGenerator $generator){
		$table=$this->metas["#tableName"];
		$primaryKeys=$this->metas["#primaryKeys"];
		$serializables=$this->getSerializableFields();
		$nullables=$this->metas["#nullable"];
		$fieldTypes=$this->metas["#fieldTypes"];
		$manyToOnes=$this->metas["#manyToOne"];
		$manyToManys=[];
		if(isset($this->metas["#manyToMany"]))
			$manyToManys=$this->metas["#manyToMany"];
		$this->scanManyToManys($generator, $manyToManys);
		$this->generatePks($generator, $primaryKeys, $table, $fieldTypes,$nullables);
		$this->generateForeignKeys($generator, $manyToOnes, $table);
		$serializables=\array_unique(\array_merge($serializables,$this->fkFieldsToAdd));
		$fieldTypes=\array_merge($fieldTypes,$this->fkFieldTypesToAdd);
		$fieldsAttributes=$this->generateFieldsAttributes($serializables, $fieldTypes, $nullables);
		$generator->createTable($table, $fieldsAttributes);
		foreach ($this->fkFieldsToAdd as $fkField){
			$generator->addKey($table, [$fkField],"");
		}
	}

	protected function getSerializableFields() {
		$notSerializable=$this->metas["#notSerializable"];
		$fieldNames=$this->metas["#fieldNames"];
		return \array_diff($fieldNames, $notSerializable);
	}

	protected function scanManyToManys(DbGenerator $generator,$manyToManys){
		foreach ($manyToManys as $member=>$manyToMany){
			if(isset($this->metas["#joinTable"][$member])){
				$annotJoinTable=$this->metas["#joinTable"][$member];
				$generator->addManyToMany($annotJoinTable["name"], $manyToMany["targetEntity"]);
			}
		}
	}

	protected function generatePks(DbGenerator $generator,$primaryKeys,$table,$fieldTypes,$nullables){
		$generator->addKey($table, $primaryKeys);
		if(\sizeof($primaryKeys)===1 && $generator->isInt($fieldTypes[$primaryKeys[0]])){
			$generator->addAutoInc($table, $this->getFieldAttributes($generator, $primaryKeys[0], $nullables, $fieldTypes));
		}
	}

	protected function generateFieldsAttributes($serializables,$fieldTypes,$nullables){
		$fieldsAttributes=[];
		foreach ($serializables as $field){
			$fieldsAttributes[]=$this->_generateFieldAttributes($field, $nullables, $fieldTypes);
		}
		return $fieldsAttributes;
	}

	public function getFieldAttributes(DbGenerator $generator,$field,$nullables,$fieldTypes){
		return $generator->generateField($this->_generateFieldAttributes($field, $nullables, $fieldTypes));
	}

	protected function _generateFieldAttributes($field,$nullables,$fieldTypes){
		$nullable="NOT NULL";
		if(\array_search($field, $nullables)!==false){
			$nullable="";
		}
		return ["name"=>$field,"type"=>$fieldTypes[$field],"extra"=>$nullable];
	}

	protected function generateForeignKey(DbGenerator $generator,$tableName,$member){
		$fieldAnnot=OrmUtils::getMemberJoinColumns("", $member,$this->metas);
		if($fieldAnnot!==null){
			$annotationArray=$fieldAnnot[1];
			$referencesTableName=OrmUtils::getTableName($annotationArray["className"]);
			$referencesFieldName=OrmUtils::getFirstKey($annotationArray["className"]);
			$fkFieldName=$fieldAnnot[0];
			$this->fkFieldsToAdd[]=$fkFieldName;
			$this->fkFieldTypesToAdd[$fkFieldName]=OrmUtils::getFieldType($annotationArray["className"], $referencesFieldName);
			$generator->addForeignKey($tableName, $fkFieldName, $referencesTableName, $referencesFieldName);
		}
	}

	protected function generateForeignKeys(DbGenerator $generator,$manyToOnes,$tableName){
		foreach ($manyToOnes as $member){
			$this->generateForeignKey($generator, $tableName, $member);
		}
	}
}
