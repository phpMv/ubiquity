<?php

namespace Ubiquity\db\utils;

class DbTypes {
	const TYPES=["tinyint"=>0,"int"=>0,"decimal"=>0,"float"=>0,"double"=>0,"smallint"=>0,"mediumint"=>0,"bigint"=>0,
			"date"=>"NULL","time"=>"NULL","datetime"=>"CURRENT_TIMESTAMP","timestamp"=>"CURRENT_TIMESTAMP","year"=>"'0000'",
			"tinytext"=>"NULL","text"=>"NULL","mediumtext"=>"NULL","longtext"=>"NULL",
			"tinyblob"=>"NULL","blob"=>"NULL","mediumblob"=>"NULL","longblob"=>"NULL",
			"char"=>"NULL","varchar"=>"NULL","binary"=>"NULL","varbinary"=>"NULL",
			"enum"=>"''","set"=>"''"
	];
	const DEFAULT_TYPE="varchar(30)";
	
	protected static $typeMatch='@([\s\S]*?)((?:\((?:\d)+\))*?)$@';
	protected static $sizeMatch='@(?:[\s\S]*?)(?:\((\d+)\))*?$@';
	protected static $intMatch='@^.*?int.*?((?:\((?:\d)+\))*?)$@';
	protected static $stringMatch='@^.*?char|text|binary.*?((?:\((?:\d)+\))*?)$@';
	
	protected static function get_($value,$regex,$pos=1){
		$matches=[];
		if (\preg_match($regex, $value,$matches)) {
			if(isset($matches[$pos])){
				return $matches[$pos];
			}
		}
		return null;
	}
	
	public static function isInt($fieldType){
		return \preg_match(self::$intMatch, $fieldType);
	}
	
	public static function isString($fieldType){
		return \preg_match(self::$stringMatch, $fieldType);
	}

	public static function getSize($dbType){
		return self::get_($dbType, self::$sizeMatch);
	}
	
	public static function getType($dbType){
		return self::get_($dbType, self::$typeMatch);
	}
}

