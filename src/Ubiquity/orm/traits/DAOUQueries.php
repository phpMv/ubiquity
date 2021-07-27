<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\db\Database;
use Ubiquity\db\SqlUtils;

/**
 * Ubiquity\orm\traits$DAOUQueries
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.3
 *
 */
trait DAOUQueries {
	protected static $annotFieldsInRelations = [];

	abstract protected static function _getAll(Database $db, $className, ConditionParser $conditionParser, $included = true, $useCache = null);

	abstract protected static function _getOne(Database $db, $className, ConditionParser $conditionParser, $included, $useCache);

	protected static function uParse($db, $className, &$ucondition, $quote, &$fields = null) {
		$expressions = self::uGetExpressions($ucondition);
		$condition = "";
		$aliases = [];
		foreach ($expressions as $expression) {
			$expressionArray = \explode(".", $expression);
			self::uParseExpression($db, $className, $expression, $expressionArray, $condition, $ucondition, $aliases, $quote, $fields);
		}
		return $condition;
	}

	protected static function uParseExpression($db, $className, $expression, &$expressionArray, &$condition, &$ucondition, &$aliases, $quote, &$fields = null) {
		$relations = self::getAnnotFieldsInRelations($className);
		$field = \array_shift($expressionArray);
		if (isset ($relations [$field])) {
			$jSQL = OrmUtils::getUJoinSQL($db, $className, $relations [$field], $field, $aliases, $quote);
			$condition .= ' ' . $jSQL ['sql'];
			if (\count($expressionArray) === 1) {
				$ucondition = \preg_replace('/(^|\s|\()' . $expression . '/', "\$1{$jSQL['alias']}." . $expressionArray [0], $ucondition);
				$fields[$expression] = $jSQL['alias'] . '.' . $expressionArray[0];
			} else {
				self::uParseExpression($db, $jSQL ['class'], $expression, $expressionArray, $condition, $ucondition, $aliases, $quote, $fields);
			}
		}
	}

	protected static function getAnnotFieldsInRelations($className) {
		if (!isset (self::$annotFieldsInRelations [$className])) {
			return self::$annotFieldsInRelations [$className] = OrmUtils::getAnnotFieldsInRelations($className);
		}
		return self::$annotFieldsInRelations [$className];
	}

	protected static function uGetExpressions($condition) {
		$condition = \preg_replace('@(["\']([^"\']|""|\'\')*["\'])@', "%values%", $condition);
		\preg_match_all('@[a-zA-Z_$][a-zA-Z_$0-9]*(?:\.[a-zA-Z_$\*][a-zA-Z_$0-9\*]*)+@', $condition, $matches);
		if (\count($matches) > 0) {
			return \array_unique($matches [0]);
		}
		return [];
	}

	/**
	 * Returns an array of $className objects from the database
	 *
	 * @param string $className class name of the model to load
	 * @param string $ucondition UQL condition
	 * @param boolean|array $included if true, loads associated members with associations, if array, example : ["client.*","commands"]
	 * @param array|null $parameters the request parameters
	 * @param boolean $useCache use the active cache if true
	 * @return array
	 */
	public static function uGetAll($className, $ucondition = '', $included = true, $parameters = null, $useCache = null) {
		$db = self::getDb($className);
		$firstPart = self::uParse($db, $className, $ucondition, $db->quote);
		return self::_getAll($db, $className, new ConditionParser ($ucondition, $firstPart, $parameters), $included, $useCache);
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
		$db = self::getDb($className);
		$quote = $db->quote;
		$condition = self::uParse($db, $className, $ucondition, $quote);
		$tableName = OrmUtils::getTableName($className);
		if ($ucondition != '') {
			$ucondition = SqlUtils::checkWhere($ucondition);
		}
		return $db->prepareAndFetchColumn("SELECT COUNT(*) FROM {$quote}{$tableName}{$quote} " . $condition . $ucondition, $parameters, 0);
	}

	
	/**
	 * @param string $className
	 * @param string $ucondition
	 * @param array|null $parameters
	 * @param array|null $groupBy
	 * @param string $function
	 * @param string $field
	 * @param bool $distinct
	 * @return array
	 */
	public static function uAggregate(string $className, string $ucondition = '', ?array $parameters = null, ?array $groupBy = null, string $function = 'COUNT', string $field = '*', bool $distinct = false): array {
		$db = self::getDb($className);
		$quote = $db->quote;

		if (\is_array($groupBy)) {
			$ucondition .= ' GROUP BY ' . \implode(', ', $groupBy);
		}
		$fieldsMap = [];
		$firstPart = self::uParse($db, $className, $ucondition, $quote, $fieldsMap);

		$field = $fieldsMap[$field] ?? $field;
		if ($distinct) {
			$field = 'DISTINCT ' . $field;
		}
		$fieldsInSelect = $function . "($field) AS result";

		if (\is_array($groupBy)) {
			foreach ($groupBy as $index => $field) {
				if (\is_int($index)) {
					$fieldsInSelect .= ',' . ($fieldsMap[$field] ?? $field);
				} else {
					$fieldsInSelect .= ',' . ($fieldsMap[$field] ?? $field) . " AS {$quote}{$index}{$quote}";
				}
			}
		}
		$tableName = OrmUtils::getTableName($className);
		if ($ucondition != '' && !\str_starts_with($ucondition, ' GROUP BY')) {
			$ucondition = SqlUtils::checkWhere($ucondition);
		}
		return $db->prepareAndFetchAll("SELECT {$fieldsInSelect} FROM {$quote}{$tableName}{$quote} " . $firstPart . $ucondition, $parameters, 0);
	}

	/**
	 * @param string $className
	 * @param string $ucondition
	 * @param array|null $parameters
	 * @param array|null $groupBy
	 * @param string $countField
	 * @param bool $distinct
	 * @return array
	 */
	public static function uCountGroupBy(string $className, string $ucondition = '', ?array $parameters = null, ?array $groupBy = null, string $countField = '*', bool $distinct = false): array {
		return self::uAggregate($className, $ucondition, $parameters, $groupBy, 'COUNT', $countField, $distinct);
	}

	/**
	 * @param string $className
	 * @param string $avgField
	 * @param string $ucondition
	 * @param array|null $parameters
	 * @param array|null $groupBy
	 * @return array
	 */
	public static function uAvgGroupBy(string $className, string $avgField, string $ucondition = '', ?array $parameters = null, ?array $groupBy = null) {
		return self::uAggregate($className, $ucondition, $parameters, $groupBy, 'AVG', $avgField, false);
	}

	/**
	 * @param string $className
	 * @param string $sumField
	 * @param string $ucondition
	 * @param array|null $parameters
	 * @param array|null $groupBy
	 * @return array
	 */
	public static function uSumGroupBy(string $className, string $sumField, string $ucondition = '', ?array $parameters = null, ?array $groupBy = null) {
		return self::uAggregate($className, $ucondition, $parameters, $groupBy, 'SUM', $sumField, false);
	}

	/**
	 * @param string $className
	 * @param string $minField
	 * @param string $ucondition
	 * @param array|null $parameters
	 * @param array|null $groupBy
	 * @return array
	 */
	public static function uMinGroupBy(string $className, string $minField, string $ucondition = '', ?array $parameters = null, ?array $groupBy = null) {
		return self::uAggregate($className, $ucondition, $parameters, $groupBy, 'MIN', $minField, false);
	}

	/**
	 * @param string $className
	 * @param string $maxField
	 * @param string $ucondition
	 * @param array|null $parameters
	 * @param array|null $groupBy
	 * @return array
	 */
	public static function uMaxGroupBy(string $className, string $maxField, string $ucondition = '', ?array $parameters = null, ?array $groupBy = null) {
		return self::uAggregate($className, $ucondition, $parameters, $groupBy, 'MAX', $maxField, false);
	}

	/**
	 * Returns an instance of $className from the database, from $keyvalues values of the primary key
	 *
	 * @param String $className complete classname of the model to load
	 * @param Array|string $ucondition primary key values or condition (UQL)
	 * @param boolean|array $included if true, charges associated members with association
	 * @param array|null $parameters the request parameters
	 * @param boolean $useCache use cache if true
	 * @return object the instance loaded or null if not found
	 */
	public static function uGetOne($className, $ucondition, $included = true, $parameters = null, $useCache = null) {
		$db = self::getDb($className);
		$condition = self::uParse($db, $className, $ucondition, $db->quote);
		$conditionParser = new ConditionParser ($ucondition, $condition);
		if (\is_array($parameters)) {
			$conditionParser->setParams($parameters);
		}
		return self::_getOne($db, $className, $conditionParser, $included, $useCache);
	}
}

