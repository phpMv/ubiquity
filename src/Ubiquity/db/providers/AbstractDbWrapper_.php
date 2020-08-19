<?php

namespace Ubiquity\db\providers;

/**
 * Ubiquity\db\providers$AbstractDbWrapper
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class AbstractDbWrapper_ {
	protected $dbInstance;

	abstract public static function getAvailableDrivers();

	abstract public function connect(string $dbType, $dbName, $serverName, string $port, string $user, string $password, array $options);

	abstract public function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql');

	abstract public function ping();

	public function close() {
		$this->dbInstance = null;
	}

	/**
	 *
	 * @return object
	 */
	public function getDbInstance() {
		return $this->dbInstance;
	}

	/**
	 *
	 * @param object $dbInstance
	 */
	public function setDbInstance($dbInstance) {
		$this->dbInstance = $dbInstance;
	}
}