<?php

namespace Ubiquity\db;

use Ubiquity\utils\JArray;
use Ubiquity\orm\OrmUtils;

/**
 * Utilitaires SQL
 * @author jc
 * @version 1.0.0.3
 */
class SqlUtils {

	private static function getParameters($keyAndValues) {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=":" . $key;
		}
		return $ret;
	}

	private static function getQuotedKeys($keyAndValues, $quote="`") {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=$quote . $key . $quote;
		}
		return $ret;
	}

	public static function getWhere($keyAndValues, $quote="`") {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=$quote . $key . $quote . "= :" . $key;
		}
		return implode(" AND ", $ret);
	}

	public static function getMultiWhere($values, $field, $quote="`") {
		$ret=array ();
		foreach ( $values as $value ) {
			$ret[]=$quote . $field . $quote . "='" . $value . "'";
		}
		return implode(" OR ", $ret);
	}

	public static function getInsertFields($keyAndValues) {
		return implode(",", self::getQuotedKeys($keyAndValues));
	}

	public static function getInsertFieldsValues($keyAndValues) {
		return implode(",", self::getParameters($keyAndValues));
	}

	public static function getUpdateFieldsKeyAndValues($keyAndValues, $quote="`") {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=$quote . $key . $quote . "= :" . $key;
		}
		return implode(",", $ret);
	}

	public static function checkWhere($condition){
		$c=\strtolower($condition);
		if ($condition != '' && \strstr($c, " join ")===false){
			$condition=" WHERE " . $condition;
		}
		return $condition;
	}

	public static function getCondition($keyValues,$classname=NULL,$separator=" AND ") {
		$retArray=array ();
		if (is_array($keyValues)) {
			if(!JArray::isAssociative($keyValues)){
				if(isset($classname)){
					$keys=OrmUtils::getKeyFields($classname);
					$keyValues=\array_combine($keys, $keyValues);
				}
			}
			foreach ( $keyValues as $key => $value ) {
				$retArray[]="`" . $key . "` = '" . $value . "'";
			}
			$condition=implode($separator, $retArray);
		} else
			$condition=$keyValues;
		return $condition;
	}

	public static function getFieldList($fields){
		if(!\is_array($fields)){
			return $fields;
		}
		$result=[];
		foreach ($fields as $field) {
			$result[]= "`{$field}`";
		}
		return \implode(",", $result);
	}
}
