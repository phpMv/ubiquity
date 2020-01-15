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

		$transformers = $metaDatas ['#transformers'] [self::$transformerOp] ?? [ ];
		$query = $db->prepareAndExecute ( $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );
		if ($query && \sizeof ( $query ) > 0) {
			$accessors = $metaDatas ['#accessors'];
			$object = self::sloadObjectFromRow ( \current ( $query ), $className, $accessors, $transformers );

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

		$transformers = $metaDatas ['#transformers'] [self::$transformerOp] ?? [ ];
		$query = $db->prepareAndExecute ( $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );

		$propsKeys = OrmUtils::getPropKeys ( $className );
		$accessors = $metaDatas ['#accessors'];
		foreach ( $query as $row ) {
			$object = self::sloadObjectFromRow ( $row, $className, $accessors, $transformers );
			$key = OrmUtils::getPropKeyValues ( $object, $propsKeys );
			$objects [$key] = $object;
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}

	/**
	 *
	 * @param array $row
	 * @param string $className
	 * @param array $accessors
	 * @return object
	 */
	private static function sloadObjectFromRow($row, $className, &$accessors, &$transformers) {
		$o = new $className ();
		if (self::$useTransformers) {
			foreach ( $transformers as $field => $transformer ) {
				$transform = self::$transformerOp;
				$row [$field] = $transformer::$transform ( $row [$field] );
			}
		}
		foreach ( $row as $k => $v ) {
			if (isset ( $accessors [$k] )) {
				$accesseur = $accessors [$k];
				$o->$accesseur ( $v );
			}
			$o->_rest [$k] = $v;
		}
		return $o;
	}
}

