<?php

namespace Ubiquity\orm;

use Ubiquity\db\Database;
use Ubiquity\db\SqlUtils;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\parser\ConditionParser;

/**
 * Ubiquity\orm$SDAO
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class SDAO extends DAO {

	protected static function _getOne(Database $db, $className, ConditionParser $conditionParser, $included, $useCache) {
		$conditionParser->limitOne ();
		$object = null;

		$metaDatas = OrmUtils::getModelMetadata ( $className );
		$tableName = $metaDatas ['#tableName'];

		$query = $db->prepareAndExecute ( $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );
		if ($query && \sizeof ( $query ) > 0) {
			$object = self::sloadObjectFromRow ( \current ( $query ), $className );
			EventsManager::trigger ( DAOEvents::GET_ONE, $object, $className );
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
		$objects = array ();

		$metaDatas = OrmUtils::getModelMetadata ( $className );
		$tableName = $metaDatas ['#tableName'];

		$query = $db->prepareAndExecute ( $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );

		foreach ( $query as $row ) {
			$objects [] = self::sloadObjectFromRow ( $row, $className );
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}

	private static function sloadObjectFromRow($row, $className) {
		$o = new $className ();
		foreach ( $row as $k => $v ) {
			$o->$k = $v;
		}
		return $o;
	}
}

