<?php

namespace Ubiquity\db\reverse;

use Ubiquity\orm\reverse\TableReversor;
use Ubiquity\orm\OrmUtils;
use Ubiquity\cache\ClassUtils;

class DbGenerator {
	protected $nameProtection;
	protected $createDatabaseMask;
	protected $createTableMask;
	protected $fieldMask;
	protected $foreignKeyMask;
	protected $alterTableMask;
	protected $alterTableAddKey;
	protected $autoIncMask;
	protected $selectDbMask;
	protected $constraintNames=[];
	protected $sqlScript=[];
	protected $fieldTypes;
	protected $defaultType;
	protected $typeMatch='@([\s\S]*?)((?:\((?:\d)+\))*?)$@';
	protected $intMatch='@^.*?int.*?((?:\((?:\d)+\))*?)$@';
	protected $manyToManys=[];


	public function isInt($fieldType){
		return \preg_match($this->intMatch, $fieldType);
	}
	public function __construct(){
		$this->nameProtection="`";
		$this->createDatabaseMask="CREATE DATABASE %name%";
		$this->selectDbMask="USE %name%";
		$this->createTableMask="CREATE TABLE %name% (%fields%) %attributes%";
		$this->fieldMask="%name% %type% %extra%";
		$this->alterTableMask="ALTER TABLE %tableName% %alter%";
		$this->foreignKeyMask="ALTER TABLE %tableName% ADD CONSTRAINT %fkName% FOREIGN KEY (%fkFieldName%) REFERENCES %referencesTableName% (%referencesFieldName%) ON DELETE %onDelete% ON UPDATE %onUpdate%";
		$this->alterTableAddKey="ALTER TABLE %tableName% ADD %type% KEY (%pkFields%)";
		$this->autoIncMask="ALTER TABLE %tableName% MODIFY %field% AUTO_INCREMENT, AUTO_INCREMENT=%value%";
		$this->fieldTypes=["tinyint"=>0,"int"=>0,"decimal"=>0,"float"=>0,"double"=>0,"smallint"=>0,"mediumint"=>0,"bigint"=>0,
							"date"=>"NULL","time"=>"NULL","datetime"=>"CURRENT_TIMESTAMP","timestamp"=>"CURRENT_TIMESTAMP","year"=>"'0000'",
							"tinytext"=>"NULL","text"=>"NULL","mediumtext"=>"NULL","longtext"=>"NULL",
							"tinyblob"=>"NULL","blob"=>"NULL","mediumblob"=>"NULL","longblob"=>"NULL",
							"char"=>"NULL","varchar"=>"NULL","binary"=>"NULL","varbinary"=>"NULL",
							"enum"=>"''","set"=>"''"
		];
		$this->defaultType="varchar(30)";
	}

	public function createDatabase($name){
		$script= $this->replaceMask("name", $name, $this->createDatabaseMask);
		return $this->addScript("head", $script);
	}

	public function selectDatabase($name){
		$script= $this->replaceMask("name", $name, $this->selectDbMask);
		return $this->addScript("head", $script);
	}

	public function createTable($name,$fieldsAttributes,$attributes=["ENGINE=InnoDB","DEFAULT CHARSET=utf8"]){
		$fields=$this->generateFields($fieldsAttributes);
		$attributes=\implode(" ", $attributes);
		$script=$this->replaceArrayMask(["name"=>$name,"fields"=>$fields,"attributes"=>$attributes], $this->createTableMask);
		return $this->addScript("body", $script);
	}

	public function addKey($tableName,$fieldNames,$type="PRIMARY"){
		$pks=[];
		foreach ($fieldNames as $fieldName){
			$pks[]=$this->nameProtection.$fieldName.$this->nameProtection;
		}
		$script= $this->replaceArrayMask(["tableName"=>$tableName,"pkFields"=>\implode(",", $pks),"type"=>$type], $this->alterTableAddKey);
		return $this->addScript("before-constraints", $script);
	}

	public function addForeignKey($tableName,$fkFieldName,$referencesTableName,$referencesFieldName,$fkName=null,$onDelete="CASCADE",$onUpdate="NO ACTION"){
		if(!isset($fkName)){
			$fkName=$this->checkConstraintName("fk_".$tableName."_".$referencesTableName);
		}
		$script= $this->replaceArrayMask(["tableName"=>$tableName,"fkName"=>$fkName,"fkFieldName"=>$fkFieldName,"referencesTableName"=>$referencesTableName,"referencesFieldName"=>$referencesFieldName,"onDelete"=>$onDelete,"onUpdate"=>$onUpdate], $this->foreignKeyMask);
		return $this->addScript("constraints", $script);
	}

	public function addAutoInc($tableName,$fieldName,$value=1){
		$script= $this->replaceArrayMask(["tableName"=>$tableName,"field"=>$fieldName,"value"=>$value], $this->autoIncMask);
		return $this->addScript("before-constraints", $script);
	}

	protected function addScript($key,$script){
		if(!isset($this->sqlScript[$key])){
			$this->sqlScript[$key]=[];
		}
		$this->sqlScript[$key][]=$script;
		return $script;
	}

	protected function checkConstraintName($name){
		if(\array_search($name, $this->constraintNames)){
			$matches=[];
			if (\preg_match('@([\s\S]*?)((?:\d)+)$@', $name,$matches)) {
				if(isset($matches[2])){
					$nb=\intval($matches[2])+1;
					$name= $matches[1].$nb;
				}
			}else{
				$name= $name."1";
			}
		}
		$this->constraintNames[]=$name;
		return $name;
	}

	public function generateField($fieldAttributes){
		$fieldAttributes=$this->checkFieldAttributes($fieldAttributes);
		return $this->replaceArrayMask($fieldAttributes,$this->fieldMask);
	}

	protected function checkFieldAttributes($fieldAttributes){
		$result=$fieldAttributes;
		$type=$fieldAttributes["type"];
		$existingType=false;
		$matches=[];
		if (\preg_match($this->typeMatch, $type,$matches)) {
			if(isset($matches[1])){
				$strType=$matches[1];
				if(isset($this->fieldTypes[$strType])){
					if(!isset($fieldAttributes["extra"]) || $fieldAttributes["extra"]=="") {
						$result["extra"]="DEFAULT ".$this->fieldTypes[$strType];
					}
					$existingType=true;
				}
			}
		}
		if(!$existingType){
			$result["type"]=$this->defaultType;
		}
		return $result;
	}

	protected function generateFields($fieldsAttributes){
		$result=[];
		foreach ($fieldsAttributes as $fieldAttribute){
			$result[]=$this->generateField($fieldAttribute);
		}
		return \implode(",", $result);
	}

	protected function replaceMask($key,$value,$mask){
		if(\strstr(\strtolower($key),"name"))
			$value=$this->nameProtection.$value.$this->nameProtection;
		return \str_replace("%".$key."%", $value, $mask);
	}

	protected function replaceArrayMask($keyValues,$mask){
		foreach ($keyValues as $key=>$value){
			$mask=$this->replaceMask($key, $value, $mask);
		}
		return $mask;
	}

	public function getSqlScript() {
		return $this->sqlScript;
	}

	public function addManyToMany($jointable,$targetEntity){
		if(!isset($this->manyToManys[$jointable])){
			$this->manyToManys[$jointable]=[];
		}
		$this->manyToManys[$jointable][]=$targetEntity;
	}

	public function generateManyToManys(){
		foreach ($this->manyToManys as $joinTable=>$targetEntities){
			$this->generateManyToMany($joinTable, $targetEntities);
		}
	}

	protected function generateManyToMany($joinTable,$targetEntities){
		$fields=[];
		$fieldTypes=[];
		$manyToOnes=[];
		$invertedJoinColumns=[];
		foreach ($targetEntities as $targetEntity){
			$pk=OrmUtils::getFirstKey($targetEntity);
			$shortClassName=ClassUtils::getClassSimpleName($targetEntity);
			$fieldName=$pk.\ucfirst($shortClassName);
			$fields[]=$fieldName;
			$type=OrmUtils::getFieldType($targetEntity, $pk);
			$fieldTypes[$fieldName]=$type;
			$memberName=\lcfirst($shortClassName);
			$manyToOnes[]=$memberName;
			$invertedJoinColumns[$fieldName]=["member"=>$memberName,"className"=>$targetEntity];
		}
		$metas=["#tableName"=>$joinTable,"#primaryKeys"=>$fields,"#nullable"=>[],
				"#notSerializable"=>[],"#fieldTypes"=>$fieldTypes,"#manyToOne"=>$manyToOnes,
				"#invertedJoinColumn"=>$invertedJoinColumns,"#oneToMany"=>[],"#joinTable"=>[],
				"#manyToMany"=>[],"#fieldNames"=>$fields
		];
		$tableGenerator=new TableReversor();
		$tableGenerator->init($metas);
		$tableGenerator->generateSQL($this);
	}

	public function __toString(){
		$scripts=\array_merge($this->sqlScript["head"],$this->sqlScript["body"]);
		$scripts=\array_merge($scripts,$this->sqlScript["before-constraints"]);
		$scripts=\array_merge($scripts,$this->sqlScript["constraints"]);
		return \implode(";\n", $scripts);
	}
}
