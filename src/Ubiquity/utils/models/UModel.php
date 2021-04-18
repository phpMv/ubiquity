<?php


namespace Ubiquity\utils\models;


/**
 * Ubiquity\utils\models$UModel
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
class UModel {
	private static function getSet(string $propertyName):array{
		$propertyName=\ucfirst($propertyName);
		return ['get'.$propertyName,'set'.$propertyName];
	}
	/**
	 * @param object $object
	 * @param string $propertyName
	 */
	public static function toggleProperty(object $object, string $propertyName):void{
		list($getter,$setter)=self::getSet($propertyName);
		$object->$setter(!($object->$getter()));
	}

	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param number $val
	 */
	public static function incProperty(object $object, string $propertyName,$val=1):void{
		list($getter,$setter)=self::getSet($propertyName);
		$object->$setter($object->$getter()+$val);
	}

	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param number $val
	 */
	public static function decProperty(object $object, string $propertyName,$val=1):void{
		list($getter,$setter)=self::getSet($propertyName);
		$object->$setter($object->$getter()-$val);
	}
	
	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param mixed $val
	 * @param bool $after
	 */
	public static function concatProperty(object $object, string $propertyName,$val,bool $after=true):void{
		list($getter,$setter)=self::getSet($propertyName);
		if($after){
			$object->$setter($object->$getter().$val);
		}else{
			$object->$setter($val.($object->$getter()));
		}
	}
}