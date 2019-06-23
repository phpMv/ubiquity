<?php

namespace Ubiquity\controllers\admin\popo;

use Ubiquity\cache\CacheManager;

class MaintenanceMode {
	private $id;
	private $excluded;
	private $controller;
	private $action;
	private $title;
	private $icon;
	private $message;
	private $until;
	private $active;

	/**
	 *
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 *
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 *
	 * @param mixed $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 *
	 * @param mixed $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 *
	 * @param mixed $active
	 */
	public function setActive($active) {
		$this->active = $active;
	}

	public static function fromArray($id, array $array) {
		$maintenance = new MaintenanceMode ();
		$maintenance->setId ( $id );
		foreach ( $array as $k => $v ) {
			$accessor = 'set' . ucfirst ( $k );
			if (method_exists ( $maintenance, $accessor )) {
				$maintenance->$accessor ( $v );
			}
		}
		return $maintenance;
	}

	public static function getActiveMaintenance(array $array) {
		$modes = $array ["modes"];
		$active = $array ["on"];
		if (isset ( $modes [$active] )) {
			$m = self::fromArray ( $active, $modes [$active] );
			$m->setActive ( true );
			return $m;
		}
		return null;
	}

	public static function manyFromArray(array $array) {
		$result = [ ];
		$modes = $array ["modes"];
		$active = $array ["on"];
		foreach ( $modes as $k => $maintArray ) {
			$maint = self::fromArray ( $k, $maintArray );
			if ($active == $k) {
				$maint->setActive ( true );
			}
			$result [$k] = $maint;
		}
		return $result;
	}

	public function activate() {
		$urls = $this->excluded ['urls'] ?? '';
		$excluded = '';
		if (is_array ( $urls )) {
			$excluded = '(?!' . implode ( '|', $urls ) . ')';
		}
		$servers = $this->excluded ['hosts'] ?? [ ];
		$ports = $this->excluded ['ports'] ?? [ ];
		CacheManager::addRoute ( '/(' . $excluded . '.*?)', $this->controller, $this->action, null, $this->getId () . '-mode', false, 10000, function ($r) use ($servers, $ports) {
			return (array_search ( $_SERVER ['SERVER_NAME'], $servers ) !== false || array_search ( $_SERVER ['SERVER_PORT'], $ports ) !== false) ? false : $r;
		} );
	}

	/**
	 *
	 * @return mixed
	 */
	public function getExcluded() {
		return $this->excluded;
	}

	/**
	 *
	 * @param mixed $excluded
	 */
	public function setExcluded($excluded) {
		$this->excluded = $excluded;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 *
	 * @param mixed $controller
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}

	/**
	 *
	 * @param mixed $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}

	public function getHosts() {
		return $this->excluded ['hosts'] ?? [ ];
	}

	public function getUrls() {
		return $this->excluded ['urls'] ?? [ ];
	}

	public function getPorts() {
		return $this->excluded ['ports'] ?? [ ];
	}

	/**
	 *
	 * @return mixed
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 *
	 * @param mixed $icon
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}

	public function getUntil() {
		return $this->until;
	}

	public function setUntil($until) {
		$this->until = $until;
	}

	public function getDuration() {
		if (isset ( $this->until )) {
			$d = new \DateTime ( $this->until );
			$value = $d->getTimestamp () - (new \DateTime ())->getTimestamp ();
			if ($value > 0)
				return $value;
		}
		return null;
	}
}

