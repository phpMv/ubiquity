<?php

namespace Ubiquity\orm\traits;

use Ubiquity\db\Database;
use Ubiquity\db\SqlUtils;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\parser\Reflexion;

/**
 * Core Trait for DAO class.
 * Ubiquity\orm\traits$DAOCoreTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.7
 *
 * @property array $db
 * @property boolean $useTransformers
 * @property string $transformerOp
 * @property array $modelsDatabase
 *
 */
trait DAOCoreTrait {
	protected static $accessors = [];
	protected static $fields = [];

	abstract public static function _affectsRelationObjects($className, $classPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache): void;

	abstract protected static function prepareManyToMany($db, &$ret, $instance, $member, $annot = null);

	abstract protected static function prepareManyToOne(&$ret, $instance, $value, $fkField, $annotationArray);

	abstract protected static function prepareOneToMany(&$ret, $instance, $member, $annot = null);

	abstract public static function _initRelationFields($included, $metaDatas, &$invertedJoinColumns, &$oneToManyFields, &$manyToManyFields): void;

	abstract public static function _getIncludedForStep($included);

	abstract protected static function getDb($model);

	protected static function getClass_($instance) {
		if (\is_object($instance)) {
			return get_class($instance);
		}
		return $instance [0];
	}

	protected static function getInstance_($instance) {
		if (\is_object($instance)) {
			return $instance;
		}
		return $instance [0];
	}

	protected static function getValue_($instance, $member) {
		if (\is_object($instance)) {
			return Reflexion::getMemberValue($instance, $member);
		}
		return $instance [1];
	}

	protected static function getFirstKeyValue_($instance) {
		if (\is_object($instance)) {
			return OrmUtils::getFirstKeyValue($instance);
		}
		return $instance [1];
	}

	protected static function _getOne(Database $db, $className, ConditionParser $conditionParser, $included, $useCache) {
		$conditionParser->limitOne();
		$included = self::_getIncludedForStep($included);
		$object = $invertedJoinColumns = $oneToManyFields = $manyToManyFields = null;

		$metaDatas = OrmUtils::getModelMetadata($className);
		$tableName = $metaDatas ['#tableName'];
		$hasIncluded = $included || (\is_array($included) && \count($included) > 0);
		if ($hasIncluded) {
			self::_initRelationFields($included, $metaDatas, $invertedJoinColumns, $oneToManyFields, $manyToManyFields);
		}
		$transformers = $metaDatas ['#transformers'] [self::$transformerOp] ?? [];
		$query = $db->prepareAndExecute($tableName, SqlUtils::checkWhere($conditionParser->getCondition()), self::_getFieldList($tableName, $metaDatas), $conditionParser->getParams(), $useCache, true);
		if ($query) {
			$oneToManyQueries = $manyToOneQueries = $manyToManyParsers = [];
			$object = self::_loadObjectFromRow($db, $query, $className, $invertedJoinColumns, $manyToOneQueries, $oneToManyFields, $manyToManyFields, $oneToManyQueries, $manyToManyParsers, $metaDatas ['#memberNames'] ?? null, $metaDatas ['#accessors'], $transformers, $metaDatas['#primaryKeys'] ?? []);
			if ($hasIncluded) {
				self::_affectsRelationObjects($className, OrmUtils::getFirstPropKey($className), $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, [$object], $included, $useCache);
			}
			EventsManager::trigger(DAOEvents::GET_ONE, $object, $className);
		}
		return $object;
	}

	/**
	 *
	 * @param Database $db
	 * @param string $className
	 * @param ConditionParser $conditionParser
	 * @param boolean|array $included
	 * @param boolean|null $useCache
	 * @return array
	 */
	protected static function _getAll(Database $db, $className, ConditionParser $conditionParser, $included = true, $useCache = null) {
		$included = self::_getIncludedForStep($included);
		$objects = [];
		$invertedJoinColumns = $oneToManyFields = $manyToManyFields = null;

		$metaDatas = OrmUtils::getModelMetadata($className);
		$primaryKeys = $metaDatas['#primaryKeys'] ?? [];
		$tableName = $metaDatas ['#tableName'];
		if ($hasIncluded = ($included || (\is_array($included) && \count($included) > 0))) {
			self::_initRelationFields($included, $metaDatas, $invertedJoinColumns, $oneToManyFields, $manyToManyFields);
		}
		$transformers = $metaDatas ['#transformers'] [self::$transformerOp] ?? [];
		$query = $db->prepareAndExecute($tableName, SqlUtils::checkWhere($conditionParser->getCondition()), self::_getFieldList($tableName, $metaDatas), $conditionParser->getParams(), $useCache);

		$oneToManyQueries = $manyToOneQueries = $manyToManyParsers = [];

		$propsKeys = OrmUtils::getPropKeys($className);
		foreach ($query as $row) {
			$object = self::_loadObjectFromRow($db, $row, $className, $invertedJoinColumns, $manyToOneQueries, $oneToManyFields, $manyToManyFields, $oneToManyQueries, $manyToManyParsers, $metaDatas ['#memberNames'] ?? null, $metaDatas ['#accessors'], $transformers, $primaryKeys);
			$objects [OrmUtils::getPropKeyValues($object, $propsKeys)] = $object;
		}
		if ($hasIncluded) {
			self::_affectsRelationObjects($className, OrmUtils::getFirstPropKey($className), $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache);
		}
		EventsManager::trigger(DAOEvents::GET_ALL, $objects, $className);
		return $objects;
	}

	public static function _getFieldList($tableName, $metaDatas) {
		return self::$fields [$tableName] ??= SqlUtils::getFieldList(\array_diff($metaDatas ['#fieldNames'], $metaDatas ['#notSerializable']), $tableName);
	}

	/**
	 *
	 * @param Database $db
	 * @param array $row
	 * @param string $className
	 * @param array $invertedJoinColumns
	 * @param array $manyToOneQueries
	 * @param array $oneToManyFields
	 * @param array $manyToManyFields
	 * @param array $oneToManyQueries
	 * @param array $manyToManyParsers
	 * @param array $memberNames
	 * @param array $accessors
	 * @param array $transformers
	 * @return object
	 */
	public static function _loadObjectFromRow(Database $db, $row, $className, $invertedJoinColumns, &$manyToOneQueries, $oneToManyFields, $manyToManyFields, &$oneToManyQueries, &$manyToManyParsers, $memberNames, $accessors, $transformers, $primaryKeys) {
		$o = new $className ();
		if (self::$useTransformers) {
			self::applyTransformers($transformers, $row, $memberNames);
		}
		foreach ($row as $k => $v) {
			if ($accesseur = ($accessors [$k] ?? false)) {
				$o->$accesseur ($v);
			}
			$o->_rest [$memberNames [$k] ?? $k] = $v;
			if (isset($primaryKeys[$k])) {
				$o->_pkv['___' . $k] = $v;
			}
			if (isset ($invertedJoinColumns) && isset ($invertedJoinColumns [$k])) {
				$fk = '_' . $k;
				$o->$fk = $v;
				self::prepareManyToOne($manyToOneQueries, $o, $v, $fk, $invertedJoinColumns [$k]);
			}
		}
		self::loadManys($o, $db, $oneToManyFields, $oneToManyQueries, $manyToManyFields, $manyToManyParsers);
		return $o;
	}

	/**
	 *
	 * @param Database $db
	 * @param array $row
	 * @param string $className
	 * @param array $memberNames
	 * @param array $transformers
	 * @return object
	 */
	public static function _loadSimpleObjectFromRow(Database $db, $row, $className, $memberNames, $transformers) {
		$o = new $className ();
		if (self::$useTransformers) {
			self::applyTransformers($transformers, $row, $memberNames);
		}
		foreach ($row as $k => $v) {
			$o->$k = $v;
			$o->_rest [$memberNames [$k] ?? $k] = $v;
		}
		return $o;
	}

	protected static function applyTransformers($transformers, &$row, $memberNames) {
		foreach ($transformers as $member => $transformer) {
			$field = \array_search($member, $memberNames);
			$transform = self::$transformerOp;
			$row [$field] = $transformer::{$transform} ($row [$field]);
		}
	}

	protected static function loadManys($o, $db, $oneToManyFields, &$oneToManyQueries, $manyToManyFields, &$manyToManyParsers) {
		if (isset ($oneToManyFields)) {
			foreach ($oneToManyFields as $k => $annot) {
				self::prepareOneToMany($oneToManyQueries, $o, $k, $annot);
			}
		}
		if (isset ($manyToManyFields)) {
			foreach ($manyToManyFields as $k => $annot) {
				self::prepareManyToMany($db, $manyToManyParsers, $o, $k, $annot);
			}
		}
	}

	private static function parseKey(&$keyValues, $className, $quote) {
		if (!\is_array($keyValues)) {
			if (\strrpos($keyValues, '=') === false && \strrpos($keyValues, '>') === false && \strrpos($keyValues, '<') === false) {
				$keyValues = $quote . OrmUtils::getFirstKey($className) . $quote . "='" . $keyValues . "'";
			}
		}
	}

	public static function storeDbCache(string $model) {
		$offset = self::$modelsDatabase [$model] ?? 'default';
		if (isset (self::$db [$offset])) {
			self::$db [$offset]->storeCache();
		}
	}

	public static function getModels($dbOffset = 'default') {
		$result = [];
		foreach (self::$modelsDatabase as $model => $offset) {
			if ($offset === $dbOffset) {
				$result[] = $model;
			}
		}
		return $result;
	}
}