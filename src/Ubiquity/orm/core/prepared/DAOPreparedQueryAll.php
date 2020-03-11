<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class DAOPreparedQueryAll extends DAOPreparedQuery {

	protected function prepare() {
		$this->conditionParser->setCondition ( $this->condition );
		parent::prepare ();
	}

	public function execute($params = [ ], $useCache = false) {
		$objects = array ();
		$invertedJoinColumns = null;

		$cp = $this->conditionParser;
		$cp->setParams ( $params );
		$className = $this->className;
		$query = $this->db->prepareAndExecute ( $this->tableName, $this->preparedCondition, $this->fieldList . $this->sqlAdditionalMembers, $cp->getParams (), $useCache );
		$oneToManyQueries = [ ];
		$manyToOneQueries = [ ];
		$manyToManyParsers = [ ];
		foreach ( $query as $row ) {
			$object = DAO::_loadObjectFromRow ( $this->db, $row, $className, $invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->accessors, $this->transformers );
			$key = OrmUtils::getPropKeyValues ( $object, $this->propsKeys );
			$this->addAditionnalMembers ( $object, $row );
			$objects [$key] = $object;
		}
		if ($this->hasIncluded) {
			DAO::_affectsRelationObjects ( $className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $this->included, $useCache );
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}
}

