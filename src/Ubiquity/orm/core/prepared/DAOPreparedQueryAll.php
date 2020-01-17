<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\db\SqlUtils;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class DAOPreparedQueryAll extends DAOPreparedQuery {

	protected function prepare() {
		$this->conditionParser->setCondition ( $this->condition );
		parent::prepare ();
	}

	public function execute($params = [], $useCache = false) {
		$objects = array ();
		$invertedJoinColumns = null;

		$cp = $this->conditionParser;
		$cp->setParams ( $params );
		$className = $this->className;
		$query = $this->db->prepareAndExecute ( $this->tableName, SqlUtils::checkWhere ( $cp->getCondition () ), $this->fieldList, $cp->getParams (), $useCache );
		$oneToManyQueries = [ ];
		$manyToOneQueries = [ ];
		$manyToManyParsers = [ ];
		foreach ( $query as $row ) {
			$object = DAO::_loadObjectFromRow ( $row, $className, $invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->accessors, $this->transformers );
			$key = OrmUtils::getPropKeyValues ( $object, $this->propsKeys );
			$objects [$key] = $object;
		}
		if ($this->hasIncluded) {
			DAO::_affectsRelationObjects ( $className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $this->included, $useCache );
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}
}

