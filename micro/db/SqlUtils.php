<?php
namespace micro\db;
/**
 * Utilitaires SQL
 * @author jc
 * @version 1.0.0.2
 * @package db
 */
class SqlUtils{

	private static function getParameters($keyAndValues){
		$ret=array();
		foreach ($keyAndValues as $key=>$value){
			$ret[]=":".$key;
		}
		return $ret;
	}
	private static function getQuotedKeys($keyAndValues,$quote="`"){
		$ret=array();
		foreach ($keyAndValues as $key=>$value){
			$ret[]=$quote.$key.$quote;
		}
		return $ret;
	}
	public static function getWhere($keyAndValues,$quote="`"){
		$ret=array();
		foreach ($keyAndValues as $key=>$value){
			$ret[]=$quote.$key.$quote."= :".$key;
		}
		return implode(" AND ", $ret);
	}

	public static function getMultiWhere($values,$field,$quote="`"){
		$ret=array();
		foreach ($values as $value){
			$ret[]=$quote.$field.$quote."='".$value."'";
		}
		return implode(" OR ", $ret);
	}

	public static function getInsertFields($keyAndValues){
		return implode(",", SqlUtils::getQuotedKeys($keyAndValues));
	}

	public static function getInsertFieldsValues($keyAndValues){
		return implode(",", SqlUtils::getParameters($keyAndValues));
	}

	public static function getUpdateFieldsKeyAndValues($keyAndValues,$quote="`"){
		$ret=array();
		foreach ($keyAndValues as $key=>$value){
			$ret[]=$quote.$key.$quote."= :".$key;
		}
		return implode(",", $ret);
	}
}