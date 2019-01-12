<?php

namespace Ubiquity\orm\traits;

trait OrmUtilsFieldsTrait {
	abstract public static function getAnnotationInfo($class, $keyAnnotation);
	abstract public static function getAnnotationInfoMember($class, $keyAnnotation, $member);
	
	public static function getFieldTypes($className) {
		$fieldTypes=self::getAnnotationInfo($className, "#fieldTypes");
		if ($fieldTypes !== false){
			return $fieldTypes;
		}
		return [ ];
	}
	
	public static function getFieldType($className,$field){
		$types= self::getFieldTypes($className);
		if(isset($types[$field])){
			return $types[$field];
		}
		return "int";
	}
	
	public static function getKeyFields($instance) {
		if(!\is_string($instance)){
			$instance=\get_class($instance);
		}
		return self::getAnnotationInfo($instance, "#primaryKeys");
	}
	
	public static function getFirstKey($class) {
		$kf=self::getAnnotationInfo($class, "#primaryKeys");
		return \current($kf);
	}
	
	public static function getAllFields($class){
		return \array_keys(self::getAnnotationInfo($class, "#fieldNames"));
	}
	
	
	public static function getFieldNames($model){
		$fields=self::getAnnotationInfo($model, "#fieldNames");
		$result=[];
		$serializables=self::getSerializableFields($model);
		foreach ($fields as $member=>$field){
			if(\array_search($member, $serializables)!==false)
				$result[$field]=$member;
		}
		return $result;
	}
	
	public static function getSerializableFields($class) {
		$notSerializable=self::getAnnotationInfo($class, "#notSerializable");
		$fieldNames=\array_keys(self::getAnnotationInfo($class, "#fieldNames"));
		return \array_diff($fieldNames, $notSerializable);
	}
	
	public static function getFormAllFields($class){
		$result=self::getSerializableFields($class);
		if ($manyToOne=self::getAnnotationInfo($class, "#manyToOne")) {
			foreach ($manyToOne as $member){
				$joinColumn = self::getAnnotationInfoMember ( $class, "#joinColumn", $member );
				$result[]=$joinColumn["name"];
			}
		}
		if ($manyToMany=self::getAnnotationInfo($class, "#manyToMany")) {
			$manyToMany=array_keys($manyToMany);
			foreach ($manyToMany as $member){
				$result[]=$member . "Ids";
			}
		}
		return $result;
	}
}

