<?php

namespace Ubiquity\utils\models;

/**
 * Ubiquity\utils\models$UArrayModels
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
class UArrayModels {
	/**
	 * Return a sorted array using a user defined comparison function.
	 * @param array $array
	 * @param callable $callback
	 * @return array
	 */
	public static function sort(array $array , callable $callback):array{
		\usort($array,$callback);
		return $array;
	}
	
	/**
	 * Group an array using an array of user defined comparison functions.
	 * @param array $objects
	 * @param array $gbCallbacks
	 * @param callable|null $lineCallback
	 * @param bool|null $sort
	 * @return array
	 */
	public static function groupsBy(array $objects, array $gbCallbacks, ?callable $lineCallback=null, ?bool $sort=null):array {
		if(\count($gbCallbacks)==0) {
			return $objects;
		}
		$objects=self::groupBy($objects, current($gbCallbacks), $lineCallback, $sort);
		\array_shift($gbCallbacks);
		$result=[];
		foreach ($objects as $k=>$v){
			$result[$k]=self::groupsBy($v,$gbCallbacks);
		}
		return $result;
	}
	
	/**
	 * Group an array using a user defined comparison function.
	 * @param array $objects
	 * @param callable $gbCallback
	 * @param callable|null $lineCallback
	 * @param bool|null $sort
	 * @return array
	 */
	public static function groupBy(array $objects, callable $gbCallback, ?callable $lineCallback=null, ?bool $sort=null):array {
		if($sort){
			$objects=self::sort($objects, function ($item1, $item2) use ($gbCallback){
				return $gbCallback($item1)<=>$gbCallback($item2);
			});
		}elseif($sort===false){
			$objects=self::sort($objects, function ($item1, $item2) use ($gbCallback){
				return $gbCallback($item2)<=>$gbCallback($item1);
			});
		}
		$result=[];
		$groupBy=null;
		foreach ($objects as $line){
			if($groupBy!==($nGB=$gbCallback($line))){
				$groupBy=$nGB;
			}
			$result[$groupBy][]=(isset($lineCallback))?$lineCallback($line):$line;
		}
		return $result;
	}
	
	/**
	 * Return an associative array of key/values from an array of objects.
	 * @param array $objects
	 * @param ?string|callable $keyFunction
	 * @param ?string|callable $valueFunction
	 * @return array
	 */
	public static function asKeyValues(array $objects, $keyFunction = NULL, $valueFunction = NULL) {
		$result = [];
		if (isset($valueFunction) === false) {
			$valueFunction = '__toString';
		}
		if (isset($keyFunction) === false) {
			foreach ($objects as $object) {
				$result[] = self::callFunction($object, $valueFunction);
			}
		} else {
			foreach ($objects as $object) {
				$result[self::callFunction($object, $keyFunction)] = self::callFunction($object, $valueFunction);
			}
		}
		return $result;
	}
	
	/**
	 * Find and return the first occurrence of the array satisfying the callback.
	 * @param array|null $objects
	 * @param callable $callback
	 * @return mixed|null
	 */
	public static function find(?array $objects,callable $callback){
		$find=false;
		if(\is_array($objects)) {
			$o = \current($objects);
			do {
				$find = $callback($o);
			} while (!$find && $o = \next($objects));
			return $o;
		}
		return null;
	}
	
	/**
	 * Remove the first occurrence of the array satisfying the callback.
	 * @param array|null $objects
	 * @param callable $callback
	 * @return array
	 */
	public static function remove(?array $objects,callable $callback):array{
		foreach ($objects as $index=>$o) {
			if($callback($o)){
				unset($objects[$index]);
				break;
			}
		}
		return $objects;
	}
	
	public static function compute(?array $objects,callable $callable,callable $computeCall){
		$res=null;
		if($objects!=null) {
			foreach ($objects as $object) {
				$computeCall($res, $callable($object));
			}
		}
		return $res;
	}
	
	public static function computeSumProperty(?array $objects,string $propertyName){
		$getter='get'.\ucfirst($propertyName);
		return self::compute($objects,fn($o)=>$o->$getter(),fn(&$r,$o)=>$r+=$o);
	}
	
	public static function computeSum(?array $objects,callable $callable){
		return self::compute($objects,$callable,fn(&$r,$o)=>$r+=$o);
	}
	
	/**
	 * Remove all the occurrences of the array satisfying the callback.
	 * @param array|null $objects
	 * @param callable $callback
	 * @return array
	 */
	public static function removeAll(?array $objects,callable $callback):array{
		$toRemove=[];
		foreach ($objects as $index=>$o) {
			if($callback($o)){
				$toRemove[]=$index;
			}
		}
		foreach ($toRemove as $index){
			unset($objects[$index]);
		}
		return $objects;
	}
	
	/**
	 * @param array $objects
	 * @return array
	 */
	public static function asArray(array $objects):array{
		$result=[];
		foreach ($objects as $index=>$o) {
			$result[$index]=$object->_rest??[];
		}
		return $result;
	}
	
	/**
	 * @param array $objects
	 * @param int options
	 * @return string
	 */
	public static function asJson(array $objects,int $options=null):string{
		$result=[];
		foreach ($objects as $index=>$o) {
			$result[$index]=$object->_rest??[];
		}
		return \json_encode($result,$options);
	}
	
	/**
	 * @param array $objects
	 * @param array $properties
	 * @return array
	 */
	public static function asArrayProperties(array $objects,array $properties):array{
		$res=[];
		$accessors=self::getAccessors($properties);
		foreach ($objects as $object){
			$or=[];
			foreach ($accessors as $prop=>$get){
				$or[$prop]=$object->$get();
			}
			$res[]=$or;
		}
		return $res;
	}
	
	/**
	 * @param array $objects
	 * @param array $properties
	 * @param int $options
	 * @return string
	 */
	public static function asJsonProperties(array $objects,array $properties,int $options=null):string{
		return \json_encode(self::asArrayProperties($objects, $properties),$options);
	}
	
	private static function getAccessors($properties,$prefix='get'){
		$res=[];
		foreach ($properties as $property){
			$res[$property]=$prefix.\ucfirst($property);
			
		}
		return $res;
	}
	
	/**
	 * @param $object
	 * @param $callback
	 * @return false|mixed
	 */
	private static function callFunction($object, $callback) {
		if (\is_string($callback)){
			return \call_user_func(array(
					$object,
					$callback
			), []);
		}
		if (\is_callable($callback)) {
			return $callback($object);
		}
		return $object;
	}
}

