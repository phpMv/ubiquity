<?php

namespace Ubiquity\orm;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\CacheManager;
use Ubiquity\orm\traits\OrmUtilsRelationsTrait;
use Ubiquity\orm\traits\OrmUtilsFieldsTrait;
use Ubiquity\controllers\rest\formatters\ResponseFormatter;

/**
 * Object/relational mapping utilities
 *
 * @author jc
 * @version 1.0.8
 */
class OrmUtils {

	use OrmUtilsFieldsTrait,OrmUtilsRelationsTrait;
	private static $modelsMetadatas;

	public static function getModelMetadata($className) {
		return self::$modelsMetadatas [$className] ??= CacheManager::getOrmModelCache ( $className );
	}

	public static function isSerializable($class, $member) {
		return ! self::_is ( $class, $member, '#notSerializable' );
	}

	public static function isNullable($class, $member) {
		return self::_is ( $class, $member, '#nullable' );
	}

	protected static function _is($class, $member, $like) {
		$ret = self::getAnnotationInfo ( $class, $like );
		if ($ret !== false) {
			return \array_search ( $member, $ret ) !== false;
		}
		return false;
	}

	public static function getFieldName($class, $member) {
		return (self::getAnnotationInfo ( $class, '#fieldNames' ) [$member]) ?? $member;
	}

	public static function getTableName($class) {
		return self::getModelMetadata ( $class ) ['#tableName'];
	}

	public static function getKeyFieldsAndValues($instance) {
		$class = \get_class ( $instance );
		return self::getFieldsAndValues_ ( $instance, self::getKeyMembers ( $class ) );
	}

	public static function getFieldsAndValues_($instance, $members) {
		$ret = [ ];
		$fieldnames = self::getAnnotationInfo ( \get_class ( $instance ), '#fieldNames' );
		foreach ( $members as $member ) {
			$v = Reflexion::getMemberValue ( $instance, $member );
			$ret [$fieldnames [$member] ?? $member] = $v;
		}
		return $ret;
	}

	public static function getKeyPropsAndValues_($instance, $props) {
		$ret = [ ];
		foreach ( $props as $prop ) {
			$v = Reflexion::getPropValue ( $instance, $prop );
			$ret [$prop->getName ()] = $v;
		}
		return $ret;
	}

	public static function getMembers($className) {
		$fieldNames = self::getAnnotationInfo ( $className, '#fieldNames' );
		if ($fieldNames !== false) {
			return \array_keys ( $fieldNames );
		}
		return [ ];
	}

	public static function getMembersAndValues($instance, $members = NULL) {
		$ret = array ();
		$className = \get_class ( $instance );
		if (\is_null ( $members ))
			$members = self::getMembers ( $className );
		foreach ( $members as $member ) {
			if (self::isSerializable ( $className, $member )) {
				$v = Reflexion::getMemberValue ( $instance, $member );
				if (self::isNotNullOrNullAccepted ( $v, $className, $member )) {
					$name = self::getFieldName ( $className, $member );
					$ret [$name] = $v;
				}
			}
		}
		return $ret;
	}

	public static function isNotNullOrNullAccepted($v, $className, $member) {
		$notNull = (isset ( $v ) && NULL !== $v && '' !== $v);
		return ($notNull) || (! $notNull && self::isNullable ( $className, $member ));
	}

	public static function getFirstKeyValue($instance) {
		$prop = OrmUtils::getFirstPropKey ( \get_class ( $instance ) );
		return $prop->getValue ( $instance );
	}

	public static function getFirstKeyValue_($instance, $members) {
		return \current ( self::getFieldsAndValues_ ( $instance, $members ) );
	}

	public static function getKeyValues($instance) {
		$fkv = self::getKeyFieldsAndValues ( $instance );
		return \implode ( '_', $fkv );
	}

	public static function getPropKeyValues($instance, $props) {
		$values = [ ];
		foreach ( $props as $prop ) {
			$values [] = $prop->getValue ( $instance );
		}
		return \implode ( '_', $values );
	}

	public static function getMembersWithAnnotation($class, $annotation) {
		return (self::getModelMetadata ( $class ) [$annotation]) ?? [ ];
	}

	/**
	 *
	 * @param object $instance
	 * @param string $memberKey
	 * @param array $array
	 * @return boolean
	 */
	public static function exists($instance, $memberKey, $array) {
		$accessor = 'get' . \ucfirst ( $memberKey );
		if (\method_exists ( $instance, $accessor )) {
			foreach ( $array as $value ) {
				if ($value->$accessor () == $instance->$accessor ())
					return true;
			}
		}
		return false;
	}

	public static function getAnnotationInfo($class, $keyAnnotation) {
		return self::getModelMetadata ( $class ) [$keyAnnotation] ?? false;
	}

	public static function getAnnotationInfoMember($class, $keyAnnotation, $member) {
		$info = self::getAnnotationInfo ( $class, $keyAnnotation );
		if ($info !== false) {
			if (! isset ( $info [0] )) { // isAssociative
				if (isset ( $info [$member] )) {
					return $info [$member];
				}
			} else {
				if (\array_search ( $member, $info ) !== false) {
					return $member;
				}
			}
		}
		return false;
	}

	public static function setFieldToMemberNames(&$fields, $relFields) {
		foreach ( $fields as $index => $field ) {
			if (isset ( $relFields [$field] )) {
				$fields [$index] = $relFields [$field];
			}
		}
	}

	public static function objectAsJSON($instance) {
		$formatter = new ResponseFormatter ();
		$datas = $formatter->cleanRestObject ( $instance );
		return $formatter->format ( [ 'pk' => self::getFirstKeyValue ( $instance ),'object' => $datas ] );
	}

	public static function getTransformers($class) {
		if (isset ( self::getModelMetadata ( $class ) ['#transformers'] ))
			return self::getModelMetadata ( $class ) ['#transformers'];
	}

	public static function getAccessors($class) {
		if (isset ( self::getModelMetadata ( $class ) ['#accessors'] ))
			return self::getModelMetadata ( $class ) ['#accessors'];
	}

	public static function clearMetaDatas() {
		self::$modelsMetadatas = [ ];
	}

	public static function hasAllMembersPublic($className) {
		$members = self::getMembers ( $className );
		foreach ( $members as $memberName ) {
			$field = new \ReflectionProperty ( $className, $memberName );
			if (! $field->isPublic ()) {
				return false;
			}
		}
		return true;
	}
}