<?php

namespace Ubiquity\utils\store;

/**
 * Data storage for async platforms
 *
 * Ubiquity\core$Application
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
class ApplicationStorage {
	/**
	 * @var array
	 */
	private static $datas;

	/**
	 * Put a value in storage at key position.
	 * @param string $key
	 * @param mixed $value
	 */
	public static function put(string $key, $value){
		self::$datas[$key]=$value;
	}

	/**
	 * Return a value by key.
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get(string $key,$default=null){
		return self::$datas[$key]??$default;
	}

	/**
	 * Return all keys in storage.
	 * @return array
	 */
	public static function getAllKeys(){
		return \array_keys(self::$datas);
	}

	/**
	 * Return all datas in storage.
	 * @return array
	 */
	public static function getAll(){
		return self::$datas;
	}

	/**
	 * Check if a key exists or not.
	 * @param string $key
	 * @return boolean
	 */
	public static function exists(string $key){
		return isset(self::$datas[$key]);
	}

	/**
	 * Search for a given value and returns the first corresponding key if successful.
	 * @param mixed $value
	 * @return string|boolean
	 */
	public static function search($value){
		return \array_search($value,self::$datas);
	}

	/**
	 * Clear all values in storage.
	 */
	public static function clear(){
		self::$datas=[];
	}

	/**
	 * Remove a value by key.
	 * @param string $key
	 */
	public static function remove(string $key){
		unset(self::$datas[$key]);
	}
}

