<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\db\Database;

/**
 * Ubiquity\orm\traits$DAOUQueries
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 */
trait DAOUQueries {
	protected static $annotFieldsInRelations = [ ];

	abstract protected static function _getAll(Database $db, $className, ConditionParser $conditionParser, $included = true, $useCache = NULL);

	abstract protected static function _getOne(Database $db, $className, ConditionParser $conditionParser, $included, $useCache);

	protected static function uParse($db, $className, &$ucondition, $quote) {
		$expressions = self::uGetExpressions ( $ucondition );
		$condition = "";
		$aliases = [ ];
		foreach ( $expressions as $expression ) {
			$expressionArray = explode ( ".", $expression );
			self::uParseExpression ( $db, $className, $expression, $expressionArray, $condition, $ucondition, $aliases, $quote );
		}
		return $condition;
	}

	protected static function uParseExpression($db, $className, $expression, &$expressionArray, &$condition, &$ucondition, &$aliases, $quote) {
		$relations = self::getAnnotFieldsInRelations ( $className );
		$field = array_shift ( $expressionArray );
		if (isset ( $relations [$field] )) {
			$jSQL = OrmUtils::getUJoinSQL ( $db, $className, $relations [$field], $field, $aliases, $quote );
			$condition .= " " . $jSQL ["sql"];
			if (sizeof ( $expressionArray ) === 1) {
				$ucondition = preg_replace ( '/(^|\s)' . $expression . '/', " {$jSQL["alias"]}." . $expressionArray [0], $ucondition );
			} else {
				self::uParseExpression ( $db, $jSQL ["class"], $expression, $expressionArray, $condition, $ucondition, $aliases, $quote );
			}
		}
	}

	protected static function getAnnotFieldsInRelations($className) {
		if (! isset ( self::$annotFieldsInRelations [$className] )) {
			return self::$annotFieldsInRelations [$className] = OrmUtils::getAnnotFieldsInRelations ( $className );
		}
		return self::$annotFieldsInRelations [$className];
	}

	protected static function uGetExpressions($condition) {
		$condition = \preg_replace ( '@(["\']([^"\']|""|\'\')*["\'])@', "%values%", $condition );
		\preg_match_all ( '@[a-zA-Z_$][a-zA-Z_$0-9]*(?:\.[a-zA-Z_$\*][a-zA-Z_$0-9\*]*)+@', $condition, $matches );
		if (\sizeof ( $matches ) > 0) {
			return \array_unique ( $matches [0] );
		}
		return [ ];
	}

	/**
	 * Returns an array of $className objects from the database
	 *
	 * @param string $className class name of the model to load
	 * @param string $ucondition UQL condition
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param array|null $parameters the request parameters
	 * @param boolean $useCache use the active cache if true
	 * @return array
	 */
	public static function uGetAll($className, $ucondition = '', $included = true, $parameters = null, $useCache = NULL) {
		$db = self::getDb ( $className );
		$firstPart = self::uParse ( $db, $className, $ucondition, $db->quote );
		return self::_getAll ( $db, $className, new ConditionParser ( $ucondition, $firstPart, $parameters ), $included, $useCache );
	}

	/**
	 * Returns the number of objects of $className from the database respecting the condition possibly passed as parameter
	 *
	 * @param string $className complete classname of the model to load
	 * @param string $ucondition Part following the WHERE of an SQL statement
	 * @param array|null $parameters The query parameters
	 * @return int|boolean count of objects
	 */
	public static function uCount($className, $ucondition = '', $parameters = null) {
		$db = self::getDb ( $className );
		$quote = $db->quote;
		$condition = self::uParse ( $db, $className, $ucondition, $quote );
		$tableName = OrmUtils::getTableName ( $className );
		if ($ucondition != '') {
			$ucondition = " WHERE " . $ucondition;
		}
		return $db->prepareAndFetchColumn ( "SELECT COUNT(*) FROM {$quote}{$tableName}{$quote} " . $condition . $ucondition, $parameters, 0 );
	}

	/**
	 * Returns an instance of $className from the database, from $keyvalues values of the primary key
	 *
	 * @param String $className complete classname of the model to load
	 * @param Array|string $ucondition primary key values or condition (UQL)
	 * @param boolean|array $included if true, charges associate members with association
	 * @param array|null $parameters the request parameters
	 * @param boolean $useCache use cache if true
	 * @return object the instance loaded or null if not found
	 */
	public static function uGetOne($className, $ucondition, $included = true, $parameters = null, $useCache = NULL) {
		$db = self::getDb ( $className );
		$condition = self::uParse ( $db, $className, $ucondition, $db->quote );
		$conditionParser = new ConditionParser ( $ucondition, $condition );
		if (is_array ( $parameters )) {
			$conditionParser->setParams ( $parameters );
		}
		return self::_getOne ( $db, $className, $conditionParser, $included, $useCache );
	}
}

