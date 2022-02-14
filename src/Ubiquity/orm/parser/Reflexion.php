<?php

namespace Ubiquity\orm\parser;

use Ubiquity\orm\OrmUtils;
use Ubiquity\cache\CacheManager;
use Ubiquity\annotations\AnnotationsEngineInterface;

/**
 * Reflection utilities
 * in dev environment only
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
class Reflexion {
	use ReflexionFieldsTrait;
	protected static $classProperties = [ ];

	public static function getMethods($instance, $filter = null) {
		$reflect = new \ReflectionClass ( $instance );
		return $reflect->getMethods ( $filter );
	}

	public static function getKeyFields($instance) {
		return self::getMembersNameWithAnnotation ( \get_class ( $instance ), 'id' );
	}

	public static function getMemberValue($instance, $member) {
		$prop = self::getProperty ( $instance, $member );
		$prop->setAccessible ( true );
		return $prop->getValue ( $instance );
	}

	public static function getPropValue($instance, $prop) {
		return $prop->getValue ( $instance );
	}

	public static function setMemberValue($instance, $member, $value):bool {
		$prop = self::getProperty ( $instance, $member );
		if ($prop) {
			$prop->setAccessible ( true );
			$prop->setValue ( $instance, $value );
			return true;
		}
		return false;
	}

	public static function getPropertiesAndValues($instance, $props = NULL) {
		$ret = [];
		$className = \get_class ( $instance );
		$modelMetas = OrmUtils::getModelMetadata ( $className );
		if (isset ( self::$classProperties [$className] )) {
			foreach ( self::$classProperties [$className] as $name => $prop ) {
				$ret [$name] = $prop->getValue ( $instance );
			}
			return $ret;
		}
		if (\is_null ( $props )) {
			$props = self::getProperties($instance);
		}
		foreach ( $props as $prop ) {
			$prop->setAccessible ( true );
			$v = $prop->getValue ( $instance );
			if (\array_search ( $prop->getName (), $modelMetas ['#notSerializable'] ) === false && OrmUtils::isNotNullOrNullAccepted ( $v, $className, $prop->getName () )) {
					$name = $modelMetas ['#fieldNames'] [$prop->getName ()] ?? $prop->getName ();
					$ret [$name] = $v;
					self::$classProperties [$className] [$name] = $prop;
			}
		}
		return $ret;
	}

	/**
	 * Returns the annotation engine (php8 attributes or php annotations).
	 *
	 * @return AnnotationsEngineInterface
	 * @since 2.4.0
	 */
	public static function getAnnotsEngine(){
		return CacheManager::getAnnotationsEngineInstance();
	}

	public static function getAnnotationClass($class, $annotation) {
		return self::getAnnotsEngine()->getAnnotsOfClass( $class, $annotation );
	}

	public static function getAnnotationMember($class, $member, $annotation) {
		$annot = self::getAnnotsEngine()->getAnnotsOfProperty( $class, $member, $annotation );
		return \current ( $annot );
	}

	public static function getAnnotationMethod($class, $method, $annotation) {
		$annot = self::getAnnotsEngine()->getAnnotsOfMethod( $class, $method, $annotation );
		return \current ( $annot );
	}

	public static function getAnnotationsMember($class, $member, $annotation) {
		return self::getAnnotsEngine()->getAnnotsOfProperty ( $class, $member, $annotation );
	}

	public static function getAnnotationsMethod($class, $method, $annotation) {
		$annotsEngine=self::getAnnotsEngine();
		if (\is_array ( $annotation )) {
			$result = [ ];
			foreach ( $annotation as $annot ) {
				$annots = $annotsEngine->getAnnotsOfMethod( $class, $method, $annot );
				if (\count ( $annots ) > 0) {
					$result = \array_merge ( $result, $annots );
				}
			}
			return $result;
		}
		$annots = $annotsEngine->getAnnotsOfMethod ( $class, $method, $annotation );
		if (\count ( $annots ) > 0){
			return $annots;
		}
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
		$ret = [];
		foreach ( $props as $prop ) {
			$annot = self::getAnnotationMember ( $class, $prop->getName (), $annotation );
			if ($annot !== false) {
				$callback ($ret, $prop, $annot);
			}
		}
		return $ret;
	}

	public static function getTableName($class) {
		$ret = self::getAnnotationClass ( $class, 'table' );
		if (\count ( $ret ) === 0) {
			$posSlash = \strrpos ( $class, '\\' );
			if ($posSlash !== false) {
				$class = \substr($class, $posSlash + 1);
			}
			$ret = $class;
		} else {
			$ret = $ret [0]->name;
		}
		return $ret;
	}

	public static function getMethodParameters(\ReflectionFunctionAbstract $method) {
		$result = [];
		foreach ( $method->getParameters () as $param ) {
			$result [] = $param->name;
		}
		return $result;
	}

	public static function getJoinTables($class) {
		$result = [ ];
		$annots = self::getMembersAnnotationWithAnnotation ( $class, 'joinTable' );
		foreach ( $annots as $annot ) {
			$result [] = $annot->name;
		}
		return $result;
	}

	public static function getAllJoinTables($models) {
		$result = [ ];
		foreach ( $models as $model ) {
			$result = \array_merge ( $result, self::getJoinTables ( $model ) );
		}
		return $result;
	}
}
