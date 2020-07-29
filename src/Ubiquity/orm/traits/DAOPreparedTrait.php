<?php
namespace Ubiquity\orm\traits;

use Ubiquity\orm\core\prepared\DAOPreparedQueryOne;
use Ubiquity\orm\core\prepared\DAOPreparedQueryById;
use Ubiquity\orm\core\prepared\DAOPreparedQueryAll;
use Ubiquity\orm\core\prepared\DAOPreparedQuery;

/**
 * Ubiquity\orm\traits$DAOPreparedTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
trait DAOPreparedTrait {

	protected static $preparedDAOQueries = [];

	public static function prepareGetById($name, $className, $included = false, $cache = null) {
		return self::$preparedDAOQueries[$name] = new DAOPreparedQueryById($className, $included, $cache);
	}

	public static function prepareGetOne($name, $className, $condition = '', $included = false, $cache = null) {
		return self::$preparedDAOQueries[$name] = new DAOPreparedQueryOne($className, $condition, $included, $cache);
	}

	public static function prepareGetAll($name, $className, $condition = '', $included = false, $cache = null) {
		return self::$preparedDAOQueries[$name] = new DAOPreparedQueryAll($className, $condition, $included, $cache);
	}

	public static function executePrepared($name, $params = [], $useCache = false) {
		if (isset(self::$preparedDAOQueries[$name])) {
			return self::$preparedDAOQueries[$name]->execute($params, $useCache);
		}
		return null;
	}

	public static function storeDbCachePrepared($name) {
		if (isset(self::$preparedDAOQueries[$name])) {
			self::$preparedDAOQueries[$name]->storeDbCache();
		}
	}

	/**
	 * Returns the daoPreparedQuery corresponding to a name
	 *
	 * @param string $name
	 * @return DAOPreparedQuery
	 */
	public static function getPrepared(string $name): DAOPreparedQuery {
		return self::$preparedDAOQueries[$name];
	}
}

