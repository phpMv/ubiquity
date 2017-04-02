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

	public static function getProperty($instance,$property){
		$reflect = new \ReflectionClass($instance);
		$prop = $reflect->getProperty($property);
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
}
