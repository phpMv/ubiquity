<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;
use Ubiquity\cache\database\DbCache;
use Ubiquity\cache\dao\AbstractDAOCache;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 */
class DAOPreparedQueryById extends DAOPreparedQuery {
	/**
	 *
	 * @var AbstractDAOCache
	 */
	protected $cache;

	public function __construct($className, $included = false, $cache = null) {
		parent::__construct ( $className, '', $included, $cache );
	}

	protected function prepare(?DbCache $cache = null) {
		parent::prepare ( $cache );
		$keys = OrmUtils::getKeyFields ( $this->className );
		$this->conditionParser->addKeyValues ( \array_fill ( 0, \count ( $keys ), '' ), $this->className );
		$this->conditionParser->limitOne ();
		$this->cache = DAO::getCache ();
	}

	public function execute($params = [ ], $useCache = false) {
		if ($useCache && isset ( $this->cache ) && ($object = $this->cache->fetch ( $this->className, \implode ( '_', $params ) ))) {
			return $object;
		}

		$cp = $this->conditionParser;
		$cp->setKeyValues ( $params );
		if ($useCache) {
			$query = $this->db->prepareAndExecute ( $this->tableName, $this->preparedCondition, $this->fieldList . $this->sqlAdditionalMembers, $cp->getParams (), $useCache );
		} else {
			$query = $this->db->prepareAndExecuteNoCache ( $this->tableName, $this->preparedCondition, $this->fieldList . $this->sqlAdditionalMembers, $cp->getParams () );
		}
		if ($query && \sizeof ( $query ) > 0) {
			$className = $this->className;
			$row = \current ( $query );
			if ($this->hasIncluded || ! $this->allPublic) {
				$oneToManyQueries = [ ];
				$manyToOneQueries = [ ];
				$manyToManyParsers = [ ];
				$object = DAO::_loadObjectFromRow ( $this->db, $row, $className, $this->invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->memberList, $this->accessors, $this->transformers );
				if ($this->hasIncluded) {
					DAO::_affectsRelationObjects ( $className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, [ $object ], $this->included, $useCache );
				}
			} else {
				$object = DAO::_loadSimpleObjectFromRow ( $this->db, $row, $className, $this->memberList, $this->transformers );
			}
			$this->addAditionnalMembers ( $object, $row );

			EventsManager::trigger ( DAOEvents::GET_ONE, $object, $className );
			return $object;
		}
		return null;
	}
}