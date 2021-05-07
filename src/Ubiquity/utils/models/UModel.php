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
	
	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param mixed $val
	 */
	public static function addTo(object $object,string $propertyName,$val):void{
		list($getter,$setter)=self::getSet($propertyName);
		$array=$object->$getter();
		$array[]=$val;
		$object->$setter($array);
	}
	
	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param mixed $val
	 * @return mixed
	 */
	public static function removeFrom(object $object,string $propertyName,$val){
		list($getter,$setter)=self::getSet($propertyName);
		$array=$object->$getter();
		if(($index=\array_search($val, $array))!==false){
			$r=$array[$index];
			unset($array[$index]);
			$object->$setter($array);
			return $r;
		}
		return false;
	}
	
	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param mixed $index
	 * @return mixed
	 */
	public static function removeFromByIndex(object $object,string $propertyName,$index){
		list($getter,$setter)=self::getSet($propertyName);
		$array=$object->$getter();
		if(isset($array[$index])){
			$r=$array[$index];
			unset($array[$index]);
			$object->$setter($array);
			return $r;
		}
		return false;
	}
	
	/**
	 * @param object $object
	 * @return array
	 */
	public static function asArray(object $object):array{
		return $object->_rest??[];
	}
	
	/**
	 * @param object $object
	 * @param int $options
	 * @return string
	 */
	public static function asJson(object $object,int $options=null):string{
		return \json_encode($object->_rest??[],$options);
	}
	
	/**
	 * @param object $object
	 * @param array $properties
	 * @return array
	 */
	public static function asArrayProperties(object $object,array $properties):array{
		$res=[];
		foreach ($properties as $property){
			$get='get'.\ucfirst($property);
			$res[$property]=$object->$get();
		}
		return $res;
	}
	
	/**
	 * @param object $object
	 * @param array $properties
	 * @param int $options
	 * @return string
	 */
	public static function asJsonProperties(object $object,array $properties,int $options=null):string{
		return \json_encode(self::asArrayProperties($object, $properties),$options);
	}
	
	/**
	 * Determines if 2 objects are equal.
	 * @param object $object
	 * @param object $toObject
	 * @param string $property
	 * @return boolean
	 */
	public static function equals(object $object,?object $toObject,string $property='id'){
		if($toObject==null){
			return false;
		}
		$get='get'.\ucfirst($property);
		return $object->$get()===$toObject->$get();
	}
}