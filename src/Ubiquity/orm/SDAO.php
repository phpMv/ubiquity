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

		$metaDatas = OrmUtils::getModelMetadata ( $className );
		$tableName = $metaDatas ['#tableName'];

		$object = $db->prepareObjectAndExecute ( $className, $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );
		if ($object) {
			EventsManager::trigger ( DAOEvents::GET_ONE, $object, $className );
			return $object;
		}
		return null;
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
		$result = array ();

		$metaDatas = OrmUtils::getModelMetadata ( $className );
		$tableName = $metaDatas ['#tableName'];

		$objects = $db->prepareObjectAndExecute ( $className, $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );

		$propsKeys = OrmUtils::getPropKeys ( $className );
		foreach ( $objects as $object ) {
			$key = OrmUtils::getPropKeyValues ( $object, $propsKeys );
			$result [$key] = $object;
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $result;
	}
}

