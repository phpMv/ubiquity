<?php

namespace Ubiquity\orm\parser;

trait ReflexionFieldsTrait {

	abstract public static function getAnnotationMember($class, $member, $annotation);

	abstract public static function getAnnotsEngine();

	/**
	 *
	 * @param string $class
	 * @param string $member
	 * @return object|boolean
	 */
	protected static function getAnnotationColumnMember($class, $member) {
		if (($r = self::getAnnotationMember ( $class, $member, 'column' )) === false) {
			$r = self::getAnnotationMember ( $class, $member, 'joinColumn' );
		}
		return $r;
	}

	public static function getDbType($class, $member) {
		$ret = self::getAnnotationColumnMember ( $class, $member );
		if (\is_object ( $ret ) && \property_exists ( $ret, 'dbType' )) {
			return $ret->dbType;
		}
		return false;
	}

	public static function isSerializable($class, $member) {
		if (self::getAnnotationMember ( $class, $member, 'transient' ) !== false || self::getAnnotationMember ( $class, $member, 'manyToOne' ) !== false || self::getAnnotationMember ( $class, $member, 'manyToMany' ) !== false || self::getAnnotationMember ( $class, $member, 'oneToMany' ) !== false)
			return false;
		else
			return true;
	}

	public static function getFieldName($class, $member) {
		$ret = self::getAnnotationColumnMember ( $class, $member );
		if ($ret === false || ! isset ( $ret->name )) {
			$ret = $member;
		} else {
			$ret = $ret->name;
		}
		return $ret;
	}

	public static function isNullable($class, $member) {
		$ret = self::getAnnotationColumnMember ( $class, $member );
		if (\is_object ( $ret ) && \property_exists ( $ret, 'nullable' )) {
			return $ret->nullable;
		}
		return false;
	}

	public static function getProperties($class) {
		$reflect = new \ReflectionClass ( $class );
		return $reflect->getProperties ();
	}

	public static function getProperty($instance, $member) {
		$reflect = new \ReflectionClass ( $instance );
		$prop = false;
		if ($reflect->hasProperty ( $member )) {
			$prop = $reflect->getProperty ( $member );
		}
		return $prop;
	}

	public static function getPropertyType($class, $property) {
		if (($r = self::getMetadata ( $class, $property, 'var', 'type' )) === false) {
			$reflect = new \ReflectionProperty ( $class, $property );
			return $reflect->getType ();
		}
		return $r;
	}

	public static function getMetadata($class, $property, $type, $name) {
		$a = self::getAnnotsEngine ()->getAnnotsOfProperty ( $class, $property, $type );
		if (! \count ( $a )) {
			return false;
		}
		return \trim ( $a [0]->$name, ';' );
	}
}

