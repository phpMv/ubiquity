<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;

/**
 * @author jc
 * @property \Ubiquity\db\Database $db
 */
trait DAOUQueries {
	
	protected static $annotFieldsInRelations=[];
	
	abstract protected static function _getAll($className, ConditionParser $conditionParser, $included=true,$useCache=NULL);
	abstract protected static function _getOne($className,ConditionParser $conditionParser,$included,$useCache);
	
	protected static function uParse($className,&$ucondition){
		$expressions=self::uGetExpressions($ucondition);
		$condition="";
		$aliases=[];
		foreach ($expressions as $expression){
			$expressionArray=explode(".",$expression);
			self::uParseExpression($className, $expression, $expressionArray, $condition, $ucondition,$aliases);
		}
		return $condition;
	}
	
	protected static function uParseExpression($className,$expression,&$expressionArray,&$condition,&$ucondition,&$aliases){
		$relations=self::getAnnotFieldsInRelations($className);
		$field=array_shift($expressionArray);
		if(isset($relations[$field])){
			$jSQL=OrmUtils::getUJoinSQL($className, $relations[$field],$field,$aliases);
			$condition.=" ".$jSQL["sql"];
			if(sizeof($expressionArray)===1){
				$ucondition=str_replace($expression, "{$jSQL["alias"]}.".$expressionArray[0], $ucondition);
			}else{
				self::uParseExpression($jSQL["class"], $expression, $expressionArray, $condition, $ucondition,$aliases);
			}
		}
	}
	
	protected static function getAnnotFieldsInRelations($className){
		if(!isset(self::$annotFieldsInRelations[$className])){
			return self::$annotFieldsInRelations[$className]=OrmUtils::getAnnotFieldsInRelations($className);
		}
		return self::$annotFieldsInRelations[$className];
		
		
	}
	
	protected static function uGetExpressions($condition){
		$condition=preg_replace('@(["\']([^"\']|""|\'\')*["\'])@', "%values%", $condition);
		preg_match_all('@[a-zA-Z_$][a-zA-Z_$0-9]*(?:\.[a-zA-Z_$\*][a-zA-Z_$0-9\*]*)+@', $condition,$matches);
		if(sizeof($matches)>0){
			return array_unique($matches[0]);
		}
		return [];
	}
	
	/**
	 * Returns an array of $className objects from the database
	 * @param string $className class name of the model to load
	 * @param string $ucondition UQL condition
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param array|null $parameters the request parameters
	 * @param boolean $useCache use the active cache if true
	 * @return array
	 */
	public static function uGetAll($className, $ucondition='', $included=true,$parameters=null,$useCache=NULL) {
		$condition=self::uParse($className, $ucondition);
		return self::_getAll($className, new ConditionParser($ucondition,$condition,$parameters),$included,$useCache);
	}
	
	/**
	 * Returns the number of objects of $className from the database respecting the condition possibly passed as parameter
	 * @param string $className complete classname of the model to load
	 * @param string $ucondition Part following the WHERE of an SQL statement
	 * @param array|null $parameters The query parameters
	 * @return int|boolean count of objects
	 */
	public static function uCount($className, $ucondition='',$parameters=null) {
		$condition=self::uParse($className, $ucondition);
		$tableName=OrmUtils::getTableName($className);
		if ($ucondition != ''){
			$ucondition=" WHERE " . $ucondition;
		}
		return self::$db->prepareAndFetchColumn("SELECT COUNT(*) FROM `" . $tableName ."` ". $condition.$ucondition,$parameters,0);
	}
	
	/**
	 * Returns an instance of $className from the database, from $keyvalues values of the primary key
	 * @param String $className complete classname of the model to load
	 * @param Array|string $ucondition primary key values or condition (UQL)
	 * @param boolean|array $included if true, charges associate members with association
	 * @param array|null $parameters the request parameters
	 * @param boolean $useCache use cache if true
	 * @return object the instance loaded or null if not found
	 */
	public static function uGetOne($className, $ucondition, $included=true,$parameters=null,$useCache=NULL) {
		$condition=self::uParse($className, $ucondition);
		$conditionParser=new ConditionParser($ucondition,$condition);
		if(is_array($parameters)){
			$conditionParser->setParams($parameters);
		}
		return self::_getOne($className, $conditionParser, $included, $useCache);
	}
}

