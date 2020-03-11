<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class DAOPreparedQueryById extends DAOPreparedQuery {

	public function __construct($className, $included = false) {
		parent::__construct ( $className, '', $included );
	}

	protected function prepare() {
		parent::prepare ();
		$keys = OrmUtils::getKeyFields ( $this->className );
		$this->conditionParser->addKeyValues ( \array_fill ( 0, \count ( $keys ), '' ), $this->className );
		$this->conditionParser->limitOne ();
	}

	public function execute($params = [ ], $useCache = false) {
		$cp = $this->conditionParser;
		$cp->setKeyValues ( $params );
		$query = $this->db->prepareAndExecute ( $this->tableName, $this->preparedCondition, $this->fieldList . $this->sqlAdditionalMembers, $cp->getParams (), $useCache );
		if ($query && \sizeof ( $query ) > 0) {
			$oneToManyQueries = [ ];
			$manyToOneQueries = [ ];
			$manyToManyParsers = [ ];
			$className = $this->className;
			$row = \current ( $query );
			$object = DAO::_loadObjectFromRow ( $this->db, $row, $className, $this->invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->accessors, $this->transformers );
			$this->addAditionnalMembers ( $object, $row );
			if ($this->hasIncluded) {
				DAO::_affectsRelationObjects ( $className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, [ $object ], $this->included, $useCache );
			}
			EventsManager::trigger ( DAOEvents::GET_ONE, $object, $className );
			return $object;
		}
		return null;
	}
}
