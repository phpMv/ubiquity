<?php

namespace Ubiquity\orm;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\base\UArray;
use Ubiquity\controllers\rest\ResponseFormatter;
use Ubiquity\orm\traits\OrmUtilsRelationsTrait;

/**
 * Object/relational mapping utilities
 * @author jc
 * @version 1.0.1
 */
class OrmUtils {
	
	use OrmUtilsRelationsTrait;
	
	private static $modelsMetadatas;

	public static function getModelMetadata($className) {
		if (!isset(self::$modelsMetadatas[$className])) {
			self::$modelsMetadatas[$className]=CacheManager::getOrmModelCache($className);
		}
		return self::$modelsMetadatas[$className];
	}

	public static function isSerializable($class, $member) {
		return !self::_is($class, $member, "#notSerializable");
	}

	public static function isNullable($class, $member) {
		return self::_is($class, $member, "#nullable");
	}
	
	protected static function _is($class,$member,$like){
		$ret=self::getAnnotationInfo($class, $like);
		if ($ret !== false){
			return \array_search($member, $ret) !== false;
		}
		return false;
	}

	public static function getFieldName($class, $member) {
		$ret=self::getAnnotationInfo($class, "#fieldNames");
		if ($ret === false || !isset($ret[$member])){
			return $member;
		}
		return $ret[$member];
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

	public static function getTableName($class) {
		if(isset(self::getModelMetadata($class)["#tableName"]))
		return self::getModelMetadata($class)["#tableName"];
	}
	
	public static function getJoinTables($class){
		$result=[];
		
		if(isset(self::getModelMetadata($class)["#joinTable"])){
			$jts=self::getModelMetadata($class)["#joinTable"];
			foreach ($jts as $jt){
				$result[]=$jt["name"];
			}
		}
		return $result;
	}
	
	public static function getAllJoinTables($models){
		$result=[];
		foreach ($models as $model){
			$result=array_merge($result,self::getJoinTables($model));
		}
		return $result;
	}

	public static function getKeyFieldsAndValues($instance) {
		$kf=self::getAnnotationInfo(get_class($instance), "#primaryKeys");
		return self::getMembersAndValues($instance, $kf);
	}

	public static function getKeyFields($instance) {
		if(!\is_string($instance)){
			$instance=\get_class($instance);
		}
		return self::getAnnotationInfo($instance, "#primaryKeys");
	}

	public static function getMembers($className) {
		$fieldNames=self::getAnnotationInfo($className, "#fieldNames");
		if ($fieldNames !== false)
			return \array_keys($fieldNames);
		return [ ];
	}

	public static function getFieldTypes($className) {
		$fieldTypes=self::getAnnotationInfo($className, "#fieldTypes");
		if ($fieldTypes !== false)
			return $fieldTypes;
		return [ ];
	}

	public static function getFieldType($className,$field){
		$types= self::getFieldTypes($className);
		if(isset($types[$field]))
			return $types[$field];
		return "int";
	}

	public static function getMembersAndValues($instance, $members=NULL) {
		$ret=array ();
		$className=get_class($instance);
		if (is_null($members))
			$members=self::getMembers($className);
		foreach ( $members as $member ) {
			if (OrmUtils::isSerializable($className, $member)) {
				$v=Reflexion::getMemberValue($instance, $member);
				if (self::isNotNullOrNullAccepted($v, $className, $member)) {
					$name=self::getFieldName($className, $member);
					$ret[$name]=$v;
				}
			}
		}
		return $ret;
	}

	public static function isNotNullOrNullAccepted($v, $className, $member) {
		$notNull=UString::isNotNull($v);
		return ($notNull) || (!$notNull && OrmUtils::isNullable($className, $member));
	}

	public static function getFirstKey($class) {
		$kf=self::getAnnotationInfo($class, "#primaryKeys");
		return \reset($kf);
	}

	public static function getFirstKeyValue($instance) {
		$fkv=self::getKeyFieldsAndValues($instance);
		return \reset($fkv);
	}
	
	public static function getKeyValues($instance) {
		$fkv=self::getKeyFieldsAndValues($instance);
		return implode("_",$fkv);
	}

	/**
	 *
	 * @param object $instance
	 * @return mixed[]
	 */
	public static function getManyToOneMembersAndValues($instance) {
		$ret=array ();
		$class=get_class($instance);
		$members=self::getAnnotationInfo($class, "#manyToOne");
		if ($members !== false) {
			foreach ( $members as $member ) {
				$memberAccessor="get" . ucfirst($member);
				if (method_exists($instance, $memberAccessor)) {
					$memberInstance=$instance->$memberAccessor();
					if (isset($memberInstance) && is_object($memberInstance)) {
						$keyValues=self::getKeyFieldsAndValues($memberInstance);
						if (sizeof($keyValues) > 0) {
							$fkName=self::getJoinColumnName($class, $member);
							$ret[$fkName]=reset($keyValues);
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function getMembersWithAnnotation($class, $annotation) {
		if (isset(self::getModelMetadata($class)[$annotation]))
			return self::getModelMetadata($class)[$annotation];
		return [ ];
	}

	/**
	 *
	 * @param object $instance
	 * @param string $memberKey
	 * @param array $array
	 * @return boolean
	 */
	public static function exists($instance, $memberKey, $array) {
		$accessor="get" . ucfirst($memberKey);
		if (method_exists($instance, $accessor)) {
			if ($array !== null) {
				foreach ( $array as $value ) {
					if ($value->$accessor() == $instance->$accessor())
						return true;
				}
			}
		}
		return false;
	}

	public static function getJoinColumnName($class, $member) {
		$annot=self::getAnnotationInfoMember($class, "#joinColumn", $member);
		if ($annot !== false) {
			$fkName=$annot["name"];
		} else {
			$fkName="id" . ucfirst(self::getTableName(ucfirst($member)));
		}
		return $fkName;
	}

	public static function getAnnotationInfo($class, $keyAnnotation) {
		if (isset(self::getModelMetadata($class)[$keyAnnotation]))
			return self::getModelMetadata($class)[$keyAnnotation];
		return false;
	}

	public static function getAnnotationInfoMember($class, $keyAnnotation, $member) {
		$info=self::getAnnotationInfo($class, $keyAnnotation);
		if ($info !== false) {
			if(UArray::isAssociative($info)){
				if (isset($info[$member])) {
					return $info[$member];
				}
			}else{
				if(\array_search($member, $info)!==false){
					return $member;
				}
			}
		}
		return false;
	}

	public static function getSerializableFields($class) {
		$notSerializable=self::getAnnotationInfo($class, "#notSerializable");
		$fieldNames=\array_keys(self::getAnnotationInfo($class, "#fieldNames"));
		return \array_diff($fieldNames, $notSerializable);
	}
	
	public static function getAllFields($class){
		return \array_keys(self::getAnnotationInfo($class, "#fieldNames"));
	}
	
	public static function getFormAllFields($class){
		$result=self::getSerializableFields($class);
		if ($manyToOne=self::getAnnotationInfo($class, "#manyToOne")) {
			foreach ($manyToOne as $member){
				$joinColumn = OrmUtils::getAnnotationInfoMember ( $class, "#joinColumn", $member );
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
	
	public static function setFieldToMemberNames(&$fields,$relFields){
		foreach ($fields as $index=>$field){
			if(isset($relFields[$field])){
				$fields[$index]=$relFields[$field];
			}
		}
	}

	public static function objectAsJSON($instance){
		$formatter=new ResponseFormatter();
		$datas=$formatter->cleanRestObject($instance);
		return $formatter->format(["pk"=>self::getFirstKeyValue($instance),"object"=>$datas]);
	}
}
