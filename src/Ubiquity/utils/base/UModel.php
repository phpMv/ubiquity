<?php


namespace Ubiquity\utils\base;


/**
 * Ubiquity\utils\base$UModel
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
	public static function toggleProperty($object, string $propertyName):void{
		list($getter,$setter)=self::getSet($propertyName);
		$object->$setter(!($object->$getter()));
	}

	public static function incProperty($object, string $propertyName,$val=1):void{
		list($getter,$setter)=self::getSet($propertyName);
		$object->$setter($object->$getter()+$val);
	}

	public static function decProperty($object, string $propertyName,$val=1):void{
		list($getter,$setter)=self::getSet($propertyName);
		$object->$setter($object->$getter()-$val);
	}
}