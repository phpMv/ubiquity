<?php
namespace Ubiquity\orm;

use Ubiquity\db\Database;
use Ubiquity\db\SqlUtils;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\log\Logger;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\parser\Reflexion;

/**
 * DAO class for models without relationships
 * Model classes must declare public members only
 * Ubiquity\orm$SDAO
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class SDAO extends DAO {

	protected static function _getOne(Database $db, $className, ConditionParser $conditionParser, $included, $useCache) {
		$conditionParser->limitOne();
		$object = null;

		$metaDatas = OrmUtils::getModelMetadata($className);
		$tableName = $metaDatas['#tableName'];

		$query = $db->prepareAndExecute($tableName, SqlUtils::checkWhere($conditionParser->getCondition()), self::_getFieldList($tableName, $metaDatas), $conditionParser->getParams(), $useCache, true);
		if ($query) {
			$object = self::sloadObjectFromRow($query, $className);
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
	protected static function _getAll(Database $db, $className, ConditionParser $conditionParser, $included = true, $useCache = NULL) {
		$objects = array();

		$metaDatas = OrmUtils::getModelMetadata($className);
		$tableName = $metaDatas['#tableName'];

		$query = $db->prepareAndExecute($tableName, SqlUtils::checkWhere($conditionParser->getCondition()), self::_getFieldList($tableName, $metaDatas), $conditionParser->getParams(), $useCache);

		foreach ($query as $row) {
			$objects[] = self::sloadObjectFromRow($row, $className);
		}
		EventsManager::trigger(DAOEvents::GET_ALL, $objects, $className);
		return $objects;
	}

	private static function sloadObjectFromRow($row, $className) {
		$o = new $className();
		foreach ($row as $k => $v) {
			$o->$k = $v;
		}
		$o->_rest = $row;
		return $o;
	}

	/**
	 * Updates an existing $instance in the database.
	 * Be careful not to modify the primary key
	 *
	 * @param object $instance
	 *        	instance to modify
	 * @param boolean $updateMany
	 *        	Adds or updates ManyToMany members
	 */
	public static function update($instance, $updateMany = false) {
		EventsManager::trigger('dao.before.update', $instance);
		$className = \get_class($instance);
		$db = self::getDb($className);
		$quote = $db->quote;
		$tableName = OrmUtils::getTableName($className);
		$ColumnskeyAndValues = Reflexion::getPropertiesAndValues($instance);
		$keyFieldsAndValues = OrmUtils::getKeyFieldsAndValues($instance);
		$sql = "UPDATE {$quote}{$tableName}{$quote} SET " . SqlUtils::getUpdateFieldsKeyAndParams($ColumnskeyAndValues) . ' WHERE ' . SqlUtils::getWhere($keyFieldsAndValues);
		$statement = $db->getUpdateStatement($sql);
		try {
			$result = $statement->execute($ColumnskeyAndValues);
			EventsManager::trigger(DAOEvents::AFTER_UPDATE, $instance, $result);
			if (Logger::isActive()) {
				Logger::info("DAOUpdates", $sql, "update");
				Logger::info("DAOUpdates", \json_encode($ColumnskeyAndValues), "Key and values");
			}
			$instance->_rest = \array_merge($instance->_rest, $ColumnskeyAndValues);
			return $result;
		} catch (\Exception $e) {
			Logger::warn("DAOUpdates", $e->getMessage(), "update");
		}
		return false;
	}
}

