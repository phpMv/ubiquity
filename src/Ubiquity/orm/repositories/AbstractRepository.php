<?php

namespace Ubiquity\orm\repositories;

use Ubiquity\controllers\Controller;
use Ubiquity\orm\DAO;
use Ubiquity\views\View;

/**
 * A repository for managing CRUD operations on a model.
 * Ubiquity\orm\repositories$AbstractRepository
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.2
 *
 */
abstract class AbstractRepository {

	abstract protected function getModel(): string;

	/**
	 * Load all instances.
	 *
	 * @param string $condition
	 * @param array|boolean $included
	 * @param array $parameters
	 * @param bool $useCache
	 * @return array
	 */
	public function all(string $condition = '', $included = false, array $parameters = [ ], bool $useCache = false): array {
		return DAO::getAll ( $this->getModel (), $condition, $included, $parameters, $useCache );
	}

	/**
	 * Load one instance by id.
	 *
	 * @param $keyValues
	 * @param bool|array $included
	 * @param bool $useCache
	 * @return ?object
	 */
	public function byId($keyValues, $included = true, bool $useCache = false): ?object {
		return DAO::getById ( $this->getModel (), $keyValues, $included, $useCache );
	}

	/**
	 * Load one instance.
	 *
	 * @param string $condition
	 * @param bool|array $included
	 * @param array $parameters
	 * @param bool $useCache
	 * @return ?object
	 * @throws \Ubiquity\exceptions\DAOException
	 */
	public function one(string $condition = '', $included = true, array $parameters = [ ], bool $useCache = false): ?object {
		return DAO::getOne ( $this->getModel (), $condition, $included, $parameters, $useCache );
	}

	/**
	 * Insert a new instance $instance into the database.
	 *
	 * @param object $instance
	 * @param bool $insertMany
	 * @return bool
	 * @throws \Exception
	 */
	public function insert(object $instance, $insertMany = false): bool {
		return DAO::insert ( $instance, $insertMany );
	}

	/**
	 * Update an instance $instance in the database.
	 *
	 * @param object $instance
	 * @param bool $insertMany
	 * @return bool
	 */
	public function update(object $instance, $insertMany = false): bool {
		return DAO::update ( $instance, $insertMany );
	}

	/**
	 * Save (insert or update) an instance $instance in the database.
	 *
	 * @param object $instance
	 * @param bool $insertMany
	 * @return bool|int
	 */
	public function save(object $instance, $insertMany = false) {
		return DAO::save ( $instance, $insertMany );
	}

	/**
	 * Remove an instance $instance from the database.
	 *
	 * @param object $instance
	 * @return int|null
	 */
	public function remove(object $instance): ?int {
		return DAO::remove ( $instance );
	}
	
	/**
	 * Returns the number of instances.
	 * 
	 * @param string $condition
	 * @param array $parameters
	 * @return int
	 */
	public function count(string $condition='',?array $parameters=null):int {
		return DAO::count($this->getModel(),$condition,$parameters);
	}
}
