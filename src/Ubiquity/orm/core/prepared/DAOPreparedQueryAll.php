<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\cache\database\DbCache;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryAll
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.7
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
			$rows = $this->db->_optExecuteAndFetch($this->statement, $this->tableName, $this->preparedCondition, $params, $useCache);
		} else {
			$rows = $this->db->_optExecuteAndFetchNoCache($this->statement, $params);
		}
		if ($this->hasIncluded || !$this->allPublic) {
			return $this->_parseQueryResponseWithIncluded($rows, $this->className, $useCache);
		}
		return $this->_parseQueryResponse($rows, $this->className);
	}

	protected function _parseQueryResponse($rows, $className) {
		$objects = [];
		if ($this->additionalMembers) {
			foreach ($rows as $row) {
				$object = DAO::_loadSimpleObjectFromRow($this->db, $row, $className, $this->memberList, $this->transformers);
				$this->addAditionnalMembers($object, $row);
				$objects[OrmUtils::getPropKeyValues($object, $this->propsKeys)] = $object;
			}
		} else {
			foreach ($rows as $row) {
				$object = DAO::_loadSimpleObjectFromRow($this->db, $row, $className, $this->memberList, $this->transformers);
				$objects[OrmUtils::getPropKeyValues($object, $this->propsKeys)] = $object;
			}
		}
		EventsManager::trigger(DAOEvents::GET_ALL, $objects, $className);
		return $objects;
	}

	protected function _parseQueryResponseWithIncluded($rows, $className, $useCache) {
		$objects = [];
		$invertedJoinColumns = null;

		$oneToManyQueries = $manyToOneQueries = $manyToManyParsers = [];
		foreach ($rows as $row) {
			$object = DAO::_loadObjectFromRow($this->db, $row, $className, $invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->memberList, $this->accessors, $this->transformers, $this->primaryKeys);
			$key = OrmUtils::getPropKeyValues($object, $this->propsKeys);
			if ($this->additionalMembers) {
				$this->addAditionnalMembers($object, $row);
			}
			$objects[$key] = $object;
		}
		DAO::_affectsRelationObjects($className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $this->included, $useCache);
		EventsManager::trigger(DAOEvents::GET_ALL, $objects, $className);
		return $objects;
	}
}
