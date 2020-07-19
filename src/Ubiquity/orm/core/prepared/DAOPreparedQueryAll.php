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
		$this->conditionParser->setCondition ( $this->condition );
		parent::prepare ( $cache );
	}

	public function execute($params = [ ], $useCache = false) {
		$cp = $this->conditionParser;
		$cp->setParams ( $params );
		$className = $this->className;
		if ($useCache) {
			$query = $this->db->prepareAndExecute ( $this->tableName, $this->preparedCondition, $this->fieldList . $this->sqlAdditionalMembers, $cp->getParams (), $useCache );
		} else {
			$query = $this->db->prepareAndExecuteNoCache ( $this->tableName, $this->preparedCondition, $this->fieldList . $this->sqlAdditionalMembers, $cp->getParams () );
		}
		if ($this->hasIncluded || ! $this->allPublic) {
			return $this->_parseQueryResponseWithIncluded ( $query, $className, $useCache );
		}
		return $this->_parseQueryResponse ( $query, $className );
	}

	protected function _parseQueryResponse($query, $className) {
		$objects = [ ];
		foreach ( $query as $row ) {
			$object = DAO::_loadSimpleObjectFromRow ( $this->db, $row, $className, $this->memberList, $this->transformers );
			$key = OrmUtils::getPropKeyValues ( $object, $this->propsKeys );
			$this->addAditionnalMembers ( $object, $row );
			$objects [$key] = $object;
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}

	protected function _parseQueryResponseWithIncluded($query, $className, $useCache) {
		$objects = [ ];
		$invertedJoinColumns = null;

		$oneToManyQueries = [ ];
		$manyToOneQueries = [ ];
		$manyToManyParsers = [ ];
		foreach ( $query as $row ) {
			$object = DAO::_loadObjectFromRow ( $this->db, $row, $className, $invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->memberList, $this->accessors, $this->transformers );
			$key = OrmUtils::getPropKeyValues ( $object, $this->propsKeys );
			$this->addAditionnalMembers ( $object, $row );
			$objects [$key] = $object;
		}
		DAO::_affectsRelationObjects ( $className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $this->included, $useCache );
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}
}
