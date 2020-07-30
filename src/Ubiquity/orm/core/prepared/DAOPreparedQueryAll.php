<?php
namespace Ubiquity\orm\core\prepared;

use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;
use Ubiquity\cache\database\DbCache;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryAll
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.6
 *
 */
class DAOPreparedQueryAll extends DAOPreparedQuery {

	protected function prepare(?DbCache $cache = null) {
		$this->conditionParser->setCondition($this->condition);
		parent::prepare($cache);
		$this->updatePrepareStatement();
	}

	public function execute($params = [], $useCache = false) {
		if ($useCache) {
			$rows = $this->db->executeDAOStatement($this->statement, $this->tableName, $this->preparedCondition, $params, $useCache);
		} else {
			$rows = $this->db->executeDAOStatementNoCache($this->statement, $params);
		}
		if ($this->hasIncluded || ! $this->allPublic) {
			return $this->_parseQueryResponseWithIncluded($rows, $this->className, $useCache);
		}
		return $this->_parseQueryResponse($rows, $this->className);
	}

	protected function _parseQueryResponse($rows, $className) {
		$objects = [];
		foreach ($rows as $row) {
			$object = DAO::_loadSimpleObjectFromRow($this->db, $row, $className, $this->memberList, $this->transformers);
			$key = OrmUtils::getPropKeyValues($object, $this->propsKeys);
			$this->addAditionnalMembers($object, $row);
			$objects[$key] = $object;
		}
		EventsManager::trigger(DAOEvents::GET_ALL, $objects, $className);
		return $objects;
	}

	protected function _parseQueryResponseWithIncluded($rows, $className, $useCache) {
		$objects = [];
		$invertedJoinColumns = null;

		$oneToManyQueries = [];
		$manyToOneQueries = [];
		$manyToManyParsers = [];
		foreach ($rows as $row) {
			$object = DAO::_loadObjectFromRow($this->db, $row, $className, $invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->memberList, $this->accessors, $this->transformers);
			$key = OrmUtils::getPropKeyValues($object, $this->propsKeys);
			$this->addAditionnalMembers($object, $row);
			$objects[$key] = $object;
		}
		DAO::_affectsRelationObjects($className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $this->included, $useCache);
		EventsManager::trigger(DAOEvents::GET_ALL, $objects, $className);
		return $objects;
	}
}
