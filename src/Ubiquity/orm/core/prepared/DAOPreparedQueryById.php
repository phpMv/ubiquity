<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\cache\dao\AbstractDAOCache;
use Ubiquity\cache\database\DbCache;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.6
 */
class DAOPreparedQueryById extends DAOPreparedQuery {

	/**
	 *
	 * @var AbstractDAOCache
	 */
	protected $cache;

	public function __construct($className, $included = false, $cache = null) {
		parent::__construct($className, '', $included, $cache);
	}

	protected function prepare(?DbCache $cache = null) {
		parent::prepare($cache);
		$keys = OrmUtils::getKeyFields($this->className);
		$this->conditionParser->prepareKeys($keys);
		$this->conditionParser->limitOne();
		$this->cache = DAO::getCache();
		$this->updatePrepareStatement();
	}

	public function execute($params = [], $useCache = false) {
		if ($useCache && isset($this->cache) && ($object = $this->cache->fetch($this->className, \implode('_', $params)))) {
			return $object;
		}

		$params = $this->conditionParser->setKeyValues($params);
		if ($useCache) {
			$row = $this->db->_optExecuteAndFetch($this->statement, $this->tableName, $this->preparedCondition, $params, $useCache, true);
		} else {
			$row = $this->db->_optExecuteAndFetchNoCache($this->statement, $params, true);
		}
		if ($row) {
			$className = $this->className;
			if ($this->hasIncluded || !$this->allPublic) {
				$oneToManyQueries = $manyToOneQueries = $manyToManyParsers = [];
				$object = DAO::_loadObjectFromRow($this->db, $row, $className, $this->invertedJoinColumns, $manyToOneQueries, $this->oneToManyFields, $this->manyToManyFields, $oneToManyQueries, $manyToManyParsers, $this->memberList, $this->accessors, $this->transformers, $this->primaryKeys);
				if ($this->hasIncluded) {
					DAO::_affectsRelationObjects($className, $this->firstPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, [
						$object
					], $this->included, $useCache);
				}
			} else {
				$object = DAO::_loadSimpleObjectFromRow($this->db, $row, $className, $this->memberList, $this->transformers);
			}
			if ($this->additionalMembers) {
				$this->addAditionnalMembers($object, $row);
			}
			EventsManager::trigger(DAOEvents::GET_ONE, $object, $className);
			return $object;
		}
		return null;
	}
}