<?php

namespace micro\orm\parser;

use micro\utils\JArray;
use micro\cache\ClassUtils;

class ModelParser {
	protected $global;
	protected $primaryKeys;
	protected $manytoOneMembers;
	protected $oneToManyMembers;
	protected $manyToManyMembers;
	protected $joinColumnMembers;
	protected $joinTableMembers;
	protected $nullableMembers=[ ];
	protected $notSerializableMembers=[ ];
	protected $fieldNames;
	protected $fieldTypes=[];

	public function parse($modelClass) {
		$instance=new $modelClass();
		$this->primaryKeys=Reflexion::getKeyFields($instance);
		$this->oneToManyMembers=Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@oneToMany");
		$this->manytoOneMembers=Reflexion::getMembersNameWithAnnotation($modelClass, "@manyToOne");
		$this->manyToManyMembers=Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@manyToMany");
		$this->joinColumnMembers=Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@joinColumn");
		$this->joinTableMembers=Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@joinTable");
		$properties=Reflexion::getProperties($instance);
		foreach ( $properties as $property ) {
			$propName=$property->getName();
			$this->fieldNames[$propName]=Reflexion::getFieldName($modelClass, $propName);
			$nullable=Reflexion::isNullable($modelClass, $propName);
			$serializable=Reflexion::isSerializable($modelClass, $propName);
			if ($nullable)
				$this->nullableMembers[]=$propName;
			if (!$serializable)
				$this->notSerializableMembers[]=$propName;
			$type=Reflexion::getDbType($modelClass, $propName);
			if($type===false)
				$type="mixed";
			$this->fieldTypes[$propName]=$type;
		}
		$this->global["#tableName"]=Reflexion::getTableName($modelClass);
	}

	public function __toString() {
		$result=$this->global;
		$result["#primaryKeys"]=$this->primaryKeys;
		$result["#manyToOne"]=$this->manytoOneMembers;
		$result["#fieldNames"]=$this->fieldNames;
		$result["#fieldTypes"]=$this->fieldTypes;
		$result["#nullable"]=$this->nullableMembers;
		$result["#notSerializable"]=$this->notSerializableMembers;
		foreach ( $this->oneToManyMembers as $member => $annotation ) {
			$result["#oneToMany"][$member]=$annotation->getPropertiesAndValues();
		}
		foreach ( $this->manyToManyMembers as $member => $annotation ) {
			$result["#manyToMany"][$member]=$annotation->getPropertiesAndValues();
		}

		foreach ( $this->joinTableMembers as $member => $annotation ) {
			$result["#joinTable"][$member]=$annotation->getPropertiesAndValues();
		}

		foreach ( $this->joinColumnMembers as $member => $annotation ) {
			$result["#joinColumn"][$member]=$annotation->getPropertiesAndValues();
			$result["#invertedJoinColumn"][$annotation->name]=[ "member" => $member,"className" => ClassUtils::cleanClassname($annotation->className) ];
		}
		return "return " . JArray::asPhpArray($result, "array") . ";";
	}
}
