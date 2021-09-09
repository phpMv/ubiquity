<?php

namespace Ubiquity\orm\traits;

/**
 * Ubiquity\orm\traits$OrmUtilsFieldsTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.2
 *
 */
trait OrmUtilsFieldsTrait {

	abstract public static function getAnnotationInfo($class, $keyAnnotation);

	abstract public static function getAnnotationInfoMember($class, $keyAnnotation, $member);
	protected static $fieldNames = [ ];
	protected static $propFirstKeys = [ ];
	protected static $propKeys = [ ];
	protected static $accessors = [ ];

	public static function getFieldTypes($className) {
		$fieldTypes = self::getAnnotationInfo ( $className, '#fieldTypes' );
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
		return 'int';
	}

	/**
	 * Return primary key fields from instance or model class
	 *
	 * @param string|object $instance
	 * @return array|boolean
	 */
	public static function getKeyFields($instance) {
		if (! \is_string ( $instance )) {
			$instance = \get_class ( $instance );
		}
		return self::getAnnotationInfo ( $instance, '#primaryKeys' );
	}

	/**
	 * Return primary key members from instance or model class
	 *
	 * @param string|object $instance
	 * @return array
	 */
	public static function getKeyMembers($instance) {
		if (! \is_string ( $instance )) {
			$instance = \get_class ( $instance );
		}
		if($info=self::getAnnotationInfo ( $instance, '#primaryKeys' )){
			return \array_keys ( $info );
		}
		return [];
		
	}

	public static function getFirstKey($class) {
		$kf = self::getAnnotationInfo ( $class, '#primaryKeys' );
		if($kf){
			return \current ( $kf );
		}
		return '';
	}

	/**
	 *
	 * @param string $class
	 * @return \ReflectionProperty
	 */
	public static function getFirstPropKey($class) {
		if (isset ( self::$propFirstKeys [$class] )) {
			return self::$propFirstKeys [$class];
		}
		$prop = new \ReflectionProperty ( $class, \array_key_first ( self::getAnnotationInfo ( $class, '#primaryKeys' ) ) );
		$prop->setAccessible ( true );
		return self::$propFirstKeys [$class] = $prop;
	}

	public static function getPropKeys($class) {
		if (isset ( self::$propKeys [$class] )) {
			return self::$propKeys [$class];
		}
		$result = [ ];
		$pkMembers = self::getAnnotationInfo ( $class, '#primaryKeys' );
		foreach ( $pkMembers as $member => $_field ) {
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
			$accesseur = 'set' . \ucfirst ( $member );
			if (! isset ( $result [$field] ) && method_exists ( $class, $accesseur )) {
				$result [$field] = $accesseur;
			}
		}
		return self::$accessors [$class] = $result;
	}

	public static function getAllFields($class) {
		return \array_keys ( self::getAnnotationInfo ( $class, '#fieldNames' ) );
	}

	public static function getFieldNames($model) {
		if (isset ( self::$fieldNames [$model] )) {
			return self::$fieldNames [$model];
		}
		$fields = self::getAnnotationInfo ( $model, '#fieldNames' );
		$result = [ ];
		$serializables = self::getSerializableFields ( $model );
		foreach ( $fields as $member => $field ) {
			if (\array_search ( $member, $serializables ) !== false)
				$result [$field] = $member;
		}
		return self::$fieldNames [$model] = $result;
	}

	public static function getSerializableFields($class) {
		$notSerializable = self::getAnnotationInfo ( $class, '#notSerializable' );
		$fieldNames = \array_values ( self::getAnnotationInfo ( $class, '#fieldNames' ) );
		return \array_diff ( $fieldNames, $notSerializable );
	}

	public static function getNullableFields($class) {
		return self::getAnnotationInfo ( $class, '#nullable' );
	}

	public static function getSerializableMembers($class) {
		$notSerializable = self::getAnnotationInfo ( $class, '#notSerializable' );
		$memberNames = \array_keys ( self::getAnnotationInfo ( $class, '#fieldNames' ) );
		return \array_diff ( $memberNames, $notSerializable );
	}

	public static function getFormAllFields($class) {
		$result = self::getSerializableMembers ( $class );
		if ($manyToOne = self::getAnnotationInfo ( $class, '#manyToOne' )) {
			foreach ( $manyToOne as $member ) {
				$joinColumn = self::getAnnotationInfoMember ( $class, '#joinColumn', $member );
				$result [] = $joinColumn ['name'];
			}
		}
		if ($manyToMany = self::getAnnotationInfo ( $class, '#manyToMany' )) {
			$manyToMany = \array_keys ( $manyToMany );
			foreach ( $manyToMany as $member ) {
				$result [] = $member . 'Ids';
			}
		}
		return $result;
	}
}

