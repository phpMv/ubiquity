<?php

namespace micro\orm;

use micro\utils\JArray;

class ModelParser {
	protected $global;
	protected $primaryKeys;
	protected $manytoOneMembers;
	protected $oneToManyMembers;
	protected $manyToManyMembers;
	protected $joinColumnMembers;
	protected $nullableMembers;
	protected $notSerializableMembers=[];
	protected $fieldNames;

	public function parse($modelClass){
		$instance=new $modelClass();
		$this->primaryKeys=Reflexion::getKeyFields($instance);
		$this->oneToManyMembers=Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@oneToMany");
		$this->manytoOneMembers=Reflexion::getMembersNameWithAnnotation($modelClass, "@manyToOne");
		$this->manyToManyMembers=Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@manyToMany");
		$this->joinColumnMembers=Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@joinColumn");
		$properties=Reflexion::getProperties($instance);
		foreach ($properties as $property){
			$propName=$property->getName();
			$this->fieldNames[$propName]=Reflexion::getFieldName($modelClass, $propName);
			$nullable=Reflexion::isNullable($modelClass, $propName);
			$serializable=Reflexion::isSerializable($modelClass, $propName);
			if($nullable)
				$this->nullableMembers[]=$propName;
			if(!$serializable)
				$this->notSerializableMembers[]=$propName;
		}
		$this->global["#tableName"]=Reflexion::getTableName($modelClass);
	}

	public function __toString(){
		$result=$this->global;
		$result["#primaryKeys"]=$this->primaryKeys;
		$result["#manyToOne"]=$this->manytoOneMembers;
		$result["#fieldNames"]=$this->fieldNames;
		$result["#nullable"]=$this->nullableMembers;
		$result["#notSerializable"]=$this->notSerializableMembers;
		foreach ($this->oneToManyMembers as $member=>$annotation){
			$result["#oneToMany"][$member]=$annotation->getPropertiesAndValues();
		}
		foreach ($this->manyToManyMembers as $member=>$annotation){
			$result["#manyToMany"][$member]=$annotation->getPropertiesAndValues();
		}
		foreach ($this->joinColumnMembers as $member=>$annotation){
			$result["#joinColumn"][$member]=$annotation->getPropertiesAndValues();
			$result["#invertedJoinColumn"][$annotation->name]=["member"=>$member,"className"=>$annotation->className];
		}
		return "return ".JArray::asPhpArray($result,"array").";";
	}
}