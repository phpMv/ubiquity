<?php
namespace micro\orm;

use mindplay\annotations\Annotation;
use mindplay\annotations\Annotations;

/**
 * Utilitaires de Reflexion
 * @author jc
 * @version 1.0.0.1
 * @package orm
 */
class Reflexion{
	public static function getProperties($instance){
		if(\is_string($instance)){
			$instance=new $instance();
		}
		$reflect = new \ReflectionClass($instance);
		$props = $reflect->getProperties();
		return $props;
	}

	public static function getKeyFields($instance){
		return Reflexion::getMembersNameWithAnnotation(get_class($instance), "@id");
	}

	public static function getMemberValue($instance,$member){
		$prop=self::getProperty($instance, $member);
		$prop->setAccessible(true);
		return $prop->getValue($instance);
	}

	public static function getProperty($instance,$member){
		$reflect = new \ReflectionClass($instance);
		$prop = $reflect->getProperty($member);
		return $prop;
	}

	public static function getPropertiesAndValues($instance,$props=NULL){
		$ret=array();
		$className=get_class($instance);
		if(is_null($props))
			$props=self::getProperties($instance);
		foreach ($props as $prop){
			$prop->setAccessible(true);
			$v=$prop->getValue($instance);
			if(OrmUtils::isSerializable($className,$prop->getName())){
				if(($v!==null && $v!=="") || (($v===null || $v==="") && OrmUtils::isNullable($className, $prop->getName()))){
					$name=OrmUtils::getFieldName($className, $prop->getName());
					$ret[$name]=$v;
				}
			}
		}
		return $ret;
	}

	public static function getAnnotationClass($class,$annotation){
		$annot=Annotations::ofClass($class,$annotation);
		return $annot;
	}

	public static function getAnnotationMember($class,$member,$annotation){
		$annot=Annotations::ofProperty($class,$member,$annotation);
		if(\sizeof($annot)>0)
			return $annot[0];
		return false;
	}

	public static function getMembersAnnotationWithAnnotation($class,$annotation){
		$props=self::getProperties($class);
		$ret=array();
		foreach ($props as $prop){
			$annot=self::getAnnotationMember($class, $prop->getName(), $annotation);
			if($annot!==false)
				$ret[$prop->getName()]=$annot;
		}
		return $ret;
	}

	public static function getMembersWithAnnotation($class,$annotation){
		$props=self::getProperties($class);
		$ret=array();
		foreach ($props as $prop){
			$annot=self::getAnnotationMember($class, $prop->getName(), $annotation);
			if($annot!==false)
				$ret[]=$prop;
		}
		return $ret;
	}

	public static function getMembersNameWithAnnotation($class,$annotation){
		$props=self::getProperties($class);
		$ret=array();
		foreach ($props as $prop){
			$annot=self::getAnnotationMember($class, $prop->getName(), $annotation);
			if($annot!==false)
				$ret[]=$prop->getName();
		}
		return $ret;
	}

	public static function isNullable($class,$member){
		$ret=self::getAnnotationMember($class,$member,"@column");
		if (!$ret)
			return false;
		else
			return $ret->nullable;
	}

	public static function isSerializable($class,$member){
		if (self::getAnnotationMember($class,$member,"@transient")!==false || self::getAnnotationMember($class,$member,"@manyToOne")!==false ||
				self::getAnnotationMember($class,$member,"@manyToMany")!==false || self::getAnnotationMember($class,$member,"@oneToMany")!==false)
			return false;
		else
			return true;
	}

	public static function getFieldName($class,$member){
		$ret=self::getAnnotationMember($class, $member, "@column");
		if($ret===false)
			$ret=$member;
		else
			$ret=$ret->name;
		return $ret;
	}

	public static function getTableName($class){
		$ret=Reflexion::getAnnotationClass($class, "@table");
		if(\sizeof($ret)===0){
			$posSlash=strrpos($class, '\\');
			if($posSlash!==false)
				$class=substr($class,  $posSlash+ 1);
			$ret=$class;
		}
		else{
			$ret=$ret[0]->name;
		}
		return $ret;
	}
}
