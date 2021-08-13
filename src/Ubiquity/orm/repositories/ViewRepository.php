<?php

namespace Ubiquity\orm\repositories;

use Ubiquity\controllers\Controller;
use Ubiquity\orm\DAO;
use Ubiquity\views\View;

/**
 * A repository for managing CRUD operations on a model, displayed in a view.
 * Ubiquity\orm\repositories$ViewRepository
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class ViewRepository {
	private string $model;
	private View $view;

	private function setViewVarsAndReturn(string $viewVar, $instance, $r) {
		$this->view->setVar ( $viewVar, $instance );
		$this->view->setVar ( 'status', $r );
		return $r;
	}

	public function __construct(Controller $ctrl, string $model) {
		$this->view = $ctrl->getView ();
		$this->model = $model;
	}

	/**
	 * Load all instances in a view variable named all.
	 *
	 * @param string $condition
	 * @param array|boolean $included
	 * @param array $parameters
	 * @param bool $useCache
	 * @param string $viewVar
	 * @return array
	 */
	public function all(string $condition = '', $included = false, array $parameters = [ ], bool $useCache = false, string $viewVar = 'all'): array {
		$this->view->setVar ( $viewVar, $r = DAO::getAll ( $this->model, $condition, $included, $parameters, $useCache ) );
		return $r;
	}

	/**
	 * Load one instance by id in a view variable named byId.
	 *
	 * @param $keyValues
	 * @param bool|array $included
	 * @param bool $useCache
	 * @param string $viewVar
	 * @return ?object
	 */
	public function byId($keyValues, $included = true, bool $useCache = false, string $viewVar = 'byId'): ?object {
		$this->view->setVar ( $viewVar, $r = DAO::getById ( $this->model, $keyValues, $included, $useCache ) );
		return $r;
	}

	/**
	 * Load one instance in a view variable named one.
	 *
	 * @param string $condition
	 * @param bool|array $included
	 * @param array $parameters
	 * @param bool $useCache
	 * @param string $viewVar
	 * @return ?object
	 * @throws \Ubiquity\exceptions\DAOException
	 */
	public function one(string $condition = '', $included = true, array $parameters = [ ], bool $useCache = false, string $viewVar = 'one'): ?object {
		$this->view->setVar ( $viewVar, $r = DAO::getOne ( $this->model, $condition, $included, $parameters, $useCache ) );
		return $r;
	}

	/**
	 * Insert a new instance $instance into the database and add the instance in a view variable (inserted).
	 * A status variable added to the view shows whether the operation was successful.
	 *
	 * @param object $instance
	 * @param bool $insertMany
	 * @param string $viewVar
	 * @return bool
	 * @throws \Exception
	 */
	public function insert(object $instance, $insertMany = false, string $viewVar = 'inserted'): bool {
		$r = DAO::insert ( $instance, $insertMany );
		return $this->setViewVarsAndReturn ( $viewVar, $instance, $r );
	}

	/**
	 * Update an instance $instance in the database and add the instance in a view variable (updated).
	 * A status variable added to the view shows whether the operation was successful.
	 *
	 * @param object $instance
	 * @param bool $insertMany
	 * @param string $viewVar
	 * @return bool
	 */
	public function update(object $instance, $insertMany = false, string $viewVar = 'updated'): bool {
		$r = DAO::update ( $instance, $insertMany );
		return $this->setViewVarsAndReturn ( $viewVar, $instance, $r );
	}

	/**
	 * Save (insert or update) an instance $instance in the database and add the instance in a view variable (saved).
	 * A status variable added to the view shows whether the operation was successful.
	 *
	 * @param object $instance
	 * @param bool $insertMany
	 * @param string $viewVar
	 * @return bool|int
	 */
	public function save(object $instance, $insertMany = false, string $viewVar = 'saved') {
		$r = DAO::save ( $instance, $insertMany );
		return $this->setViewVarsAndReturn ( $viewVar, $instance, $r );
	}

	/**
	 * Remove an instance $instance from the database and add the instance in a view variable.
	 *
	 * @param object $instance
	 * @param string $viewVar
	 * @return int|null
	 */
	public function remove(object $instance, string $viewVar = 'removed'): ?int {
		$r = DAO::remove ( $instance );
		return $this->setViewVarsAndReturn ( $viewVar, $instance, $r );
	}
}
