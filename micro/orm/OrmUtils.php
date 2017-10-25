<?php

namespace micro\orm;

use micro\orm\parser\Reflexion;
use micro\cache\CacheManager;
use micro\utils\StrUtils;
use micro\utils\JArray;

/**
 * Utilitaires de mappage Objet/relationnel
 * @author jc
 * @version 1.0.0.5
 */
class OrmUtils {
	private static $modelsMetadatas;

	public static function getModelMetadata($className) {
		if (!isset(self::$modelsMetadatas[$className])) {
			self::$modelsMetadatas[$className]=CacheManager::createOrmModelCache($className);
		}
		return self::$modelsMetadatas[$className];
	}

	public static function isSerializable($class, $member) {
		$ret=self::getAnnotationInfo($class, "#notSerializable");
		if ($ret !== false)
			return \array_search($member, $ret) === false;
		else
			return true;
	}

	public static function isNullable($class, $member) {
		$ret=self::getAnnotationInfo($class, "#nullable");
		if ($ret !== false)
			return \array_search($member, $ret) !== false;
		else
			return false;
	}

	public static function getFieldName($class, $member) {
		$ret=self::getAnnotationInfo($class, "#fieldNames");
		if ($ret === false)
			$ret=$member;
		else
			$ret=$ret[$member];
		return $ret;
	}

	public static function getTableName($class) {
		return self::getModelMetadata($class)["#tableName"];
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
		$notNull=StrUtils::isNotNull($v);
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
					if (isset($memberInstance)) {
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
			if(JArray::isAssociative($info)){
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

	public static function getFieldsInRelations($class) {
		$result=[ ];
		if ($manyToOne=self::getAnnotationInfo($class, "#manyToOne")) {
			$result=\array_merge($result, $manyToOne);
		}
		if ($oneToMany=self::getAnnotationInfo($class, "#oneToMany")) {
			$result=\array_merge($result, \array_keys($oneToMany));
		}
		if ($manyToMany=self::getAnnotationInfo($class, "#manyToMany")) {
			$result=\array_merge($result, \array_keys($manyToMany));
		}
		return $result;
	}

	public static function getManyToOneFields($class) {
		return self::getAnnotationInfo($class, "#manyToOne");
	}

	public static function getManyToManyFields($class) {
		$result=self::getAnnotationInfo($class, "#manyToMany");
		if($result!==false)
			return \array_keys($result);
		return [];
	}

	public static function getDefaultFk($classname) {
		return "id" . \ucfirst(self::getTableName($classname));
	}
}
