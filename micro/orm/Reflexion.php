<?php
namespace micro\orm;
require_once ROOT.DS.'micro/addendum/annotations.php';

require_once ROOT.DS.'micro/annotations/Column.php';
require_once ROOT.DS.'micro/annotations/Transient.php';
require_once ROOT.DS.'micro/annotations/Table.php';
require_once ROOT.DS.'micro/annotations/Id.php';
require_once ROOT.DS.'micro/annotations/ManyToOne.php';
require_once ROOT.DS.'micro/annotations/JoinColumn.php';
require_once ROOT.DS.'micro/annotations/OneToMany.php';
require_once ROOT.DS.'micro/annotations/ManyToMany.php';
require_once ROOT.DS.'micro/annotations/JoinTable.php';


/**
 * Utilitaires de Reflexion
 * @author jc
 * @version 1.0.0.1
 * @package orm
 */
class Reflexion{
	public static function getProperties($instance){
		$reflect = new \ReflectionClass($instance);
		$props   = $reflect->getProperties();
		return $props;
	}

	public static function getPropertie($instance,$property){
		$reflect = new \ReflectionClass($instance);
		$prop   = $reflect->getPropertie($property);
		return $prop;
	}

	public static function getPropertiesAndValues($instance,$props=NULL){
		$ret=array();
		$className=get_class($instance);
		if(is_null($props))
			$props=Reflexion::getProperties($instance);
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

	public static function getAnnotationClass($class,$annotation)
	{
		$rac=new \ReflectionAnnotatedClass($class);
		$annot=$rac->getAnnotation($annotation);
		return $annot;
	}

	public static function getAnnotationMember($class,$member,$annotation)
	{
		$rap=new \ReflectionAnnotatedProperty($class, $member);
		if($rap!=null)
			$annot=$rap->getAnnotation($annotation);
		return $annot;
	}

	public static function getMembersWithAnnotation($class,$annotation){
		$props=Reflexion::getProperties(new $class());
		$ret=array();
		foreach ($props as $prop){
			$annot=Reflexion::getAnnotationMember($class, $prop->getName(), $annotation);
			if($annot!==FALSE)
				$ret[]=$prop;
		}
		return $ret;
	}
}