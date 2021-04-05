<?php

namespace Ubiquity\utils\base;

/**
 * Ubiquity\utils\base$UArrayModels
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

