<?php

namespace Ubiquity\orm\traits;

trait OrmUtilsFieldsTrait {

	abstract public static function getAnnotationInfo($class, $keyAnnotation);

	abstract public static function getAnnotationInfoMember($class, $keyAnnotation, $member);
	protected static $fieldNames = [ ];
	protected static $propFirstKeys = [ ];
	protected static $propKeys = [ ];
	protected static $accessors = [ ];

	public static function getFieldTypes($className) {
		$fieldTypes = self::getAnnotationInfo ( $className, "#fieldTypes" );
		if ($fieldTypes !== false) {
			return $fieldTypes;
		}
		return [ ];
	}

	public static function getFieldType($className, $field) {
		$types = self::getFieldTypes ( $className );
		if (isset ( $types [$field] )) {
			return $types [$field];
		}
		return "int";
	}

	public static function getKeyFields($instance) {
		if (! \is_string ( $instance )) {
			$instance = \get_class ( $instance );
		}
		return self::getAnnotationInfo ( $instance, "#primaryKeys" );
	}

	public static function getFirstKey($class) {
		$kf = self::getAnnotationInfo ( $class, "#primaryKeys" );
		return \current ( $kf );
	}

	public static function getFirstPropKey($class) {
		if (isset ( self::$propFirstKeys [$class] )) {
			return self::$propFirstKeys [$class];
		}
		$prop = new \ReflectionProperty ( $class, current ( self::getAnnotationInfo ( $class, "#primaryKeys" ) ) );
		$prop->setAccessible ( true );
		return self::$propFirstKeys [$class] = $prop;
	}

	public static function getPropKeys($class) {
		if (isset ( self::$propKeys [$class] )) {
			return self::$propKeys [$class];
		}
		$result = [ ];
		$pkMembers = self::getAnnotationInfo ( $class, "#primaryKeys" );
		foreach ( $pkMembers as $member ) {
			$prop = new \ReflectionProperty ( $class, $member );
			$prop->setAccessible ( true );
			$result [] = $prop;
		}
		return self::$propKeys [$class] = $result;
	}

	public static function getAccessors($class, $members) {
		if (isset ( self::$accessors [$class] )) {
			return self::$accessors [$class];
		}
		$result = [ ];
		foreach ( $members as $member => $field ) {
			$accesseur = "set" . ucfirst ( $member );
			if (! isset ( $result [$field] ) && method_exists ( $class, $accesseur )) {
				$result [$field] = $accesseur;
			}
		}
		return self::$accessors [$class] = $result;
	}

	public static function getAllFields($class) {
		return \array_keys ( self::getAnnotationInfo ( $class, "#fieldNames" ) );
	}

	public static function getFieldNames($model) {
		if (isset ( self::$fieldNames [$model] )) {
			return self::$fieldNames [$model];
		}
		$fields = self::getAnnotationInfo ( $model, "#fieldNames" );
		$result = [ ];
		$serializables = self::getSerializableFields ( $model );
		foreach ( $fields as $member => $field ) {
			if (\array_search ( $member, $serializables ) !== false)
				$result [$field] = $member;
		}
		return self::$fieldNames [$model] = $result;
	}

	public static function getSerializableFields($class) {
		$notSerializable = self::getAnnotationInfo ( $class, "#notSerializable" );
		$fieldNames = \array_keys ( self::getAnnotationInfo ( $class, "#fieldNames" ) );
		return \array_diff ( $fieldNames, $notSerializable );
	}

	public static function getFormAllFields($class) {
		$result = self::getSerializableFields ( $class );
		if ($manyToOne = self::getAnnotationInfo ( $class, "#manyToOne" )) {
			foreach ( $manyToOne as $member ) {
				$joinColumn = self::getAnnotationInfoMember ( $class, "#joinColumn", $member );
				$result [] = $joinColumn ["name"];
			}
		}
		if ($manyToMany = self::getAnnotationInfo ( $class, "#manyToMany" )) {
			$manyToMany = array_keys ( $manyToMany );
			foreach ( $manyToMany as $member ) {
				$result [] = $member . "Ids";
			}
		}
		return $result;
	}
}

