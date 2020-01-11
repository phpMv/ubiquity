<?php

namespace Ubiquity\orm\parser;

use mindplay\annotations\Annotations;
use Ubiquity\orm\OrmUtils;

/**
 * Reflection utilities
 * in dev environment only
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 */
class Reflexion {
	use ReflexionFieldsTrait;
	protected static $classProperties = [ ];

	public static function getMethods($instance, $filter = null) {
		$reflect = new \ReflectionClass ( $instance );
		$methods = $reflect->getMethods ( $filter );
		return $methods;
	}

	public static function getKeyFields($instance) {
		return self::getMembersNameWithAnnotation ( get_class ( $instance ), "@id" );
	}

	public static function getMemberValue($instance, $member) {
		$prop = self::getProperty ( $instance, $member );
		$prop->setAccessible ( true );
		return $prop->getValue ( $instance );
	}

	public static function getPropValue($instance, $prop) {
		return $prop->getValue ( $instance );
	}

	public static function setMemberValue($instance, $member, $value) {
		$prop = self::getProperty ( $instance, $member );
		if ($prop) {
			$prop->setAccessible ( true );
			$prop->setValue ( $instance, $value );
			return true;
		}
		return false;
	}

	public static function getPropertiesAndValues($instance, $props = NULL) {
		$ret = array ();
		$className = \get_class ( $instance );
		if (isset ( self::$classProperties [$className] )) {
			foreach ( self::$classProperties [$className] as $prop ) {
				$ret [$prop->getName ()] = $prop->getValue ( $instance );
			}
			return $ret;
		}
		if (\is_null ( $props ))
			$props = self::getProperties ( $instance );
		foreach ( $props as $prop ) {
			$prop->setAccessible ( true );
			$v = $prop->getValue ( $instance );
			if (OrmUtils::isSerializable ( $className, $prop->getName () )) {
				if (OrmUtils::isNotNullOrNullAccepted ( $v, $className, $prop->getName () )) {
					$name = OrmUtils::getFieldName ( $className, $prop->getName () );
					$ret [$name] = $v;
					self::$classProperties [$className] [] = $prop;
				}
			}
		}
		return $ret;
	}

	public static function getAnnotationClass($class, $annotation) {
		$annot = Annotations::ofClass ( $class, $annotation );
		return $annot;
	}

	public static function getAnnotationMember($class, $member, $annotation) {
		$annot = Annotations::ofProperty ( $class, $member, $annotation );
		return current ( $annot );
	}

	public static function getAnnotationsMember($class, $member, $annotation) {
		return Annotations::ofProperty ( $class, $member, $annotation );
	}

	public static function getAnnotationsMethod($class, $method, $annotation) {
		if (is_array ( $annotation )) {
			$result = [ ];
			foreach ( $annotation as $annot ) {
				$annots = Annotations::ofMethod ( $class, $method, $annot );
				if (sizeof ( $annots ) > 0) {
					$result = array_merge ( $result, $annots );
				}
			}
			return $result;
		}
		$annots = Annotations::ofMethod ( $class, $method, $annotation );
		if (\sizeof ( $annots ) > 0)
			return $annots;
		return false;
	}

	public static function getMembersAnnotationWithAnnotation($class, $annotation) {
		return self::getMembersWithAnnotation_ ( $class, $annotation, function (&$ret, $prop, $annot) {
			$ret [$prop->getName ()] = $annot;
		} );
	}

	public static function getMembersWithAnnotation($class, $annotation) {
		return self::getMembersWithAnnotation_ ( $class, $annotation, function (&$ret, $prop) {
			$ret [] = $prop;
		} );
	}

	public static function getMembersNameWithAnnotation($class, $annotation) {
		return self::getMembersWithAnnotation_ ( $class, $annotation, function (&$ret, $prop) {
			$ret [] = $prop->getName ();
		} );
	}

	protected static function getMembersWithAnnotation_($class, $annotation, $callback) {
		$props = self::getProperties ( $class );
		$ret = array ();
		foreach ( $props as $prop ) {
			$annot = self::getAnnotationMember ( $class, $prop->getName (), $annotation );
			if ($annot !== false)
				$callback ( $ret, $prop, $annot );
		}
		return $ret;
	}

	public static function getTableName($class) {
		$ret = Reflexion::getAnnotationClass ( $class, "@table" );
		if (\sizeof ( $ret ) === 0) {
			$posSlash = strrpos ( $class, '\\' );
			if ($posSlash !== false)
				$class = substr ( $class, $posSlash + 1 );
			$ret = $class;
		} else {
			$ret = $ret [0]->name;
		}
		return $ret;
	}

	public static function getMethodParameters(\ReflectionFunctionAbstract $method) {
		$result = array ();
		foreach ( $method->getParameters () as $param ) {
			$result [] = $param->name;
		}
		return $result;
	}

	public static function getJoinTables($class) {
		$result = [ ];
		$annots = self::getMembersAnnotationWithAnnotation ( $class, "@joinTable" );
		foreach ( $annots as $annot ) {
			$result [] = $annot->name;
		}
		return $result;
	}

	public static function getAllJoinTables($models) {
		$result = [ ];
		foreach ( $models as $model ) {
			$result = array_merge ( $result, self::getJoinTables ( $model ) );
		}
		return $result;
	}
}
