<?php

namespace Ubiquity\orm\traits;

/**
 * Ubiquity\orm\traits$DAOTransactionsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @property \Ubiquity\db\Database $db
 *
 */
trait DAOTransactionsTrait {

	/**
	 * Initiates a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function beginTransaction() {
		return self::$db->beginTransaction ();
	}

	/**
	 * Commits a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function commit() {
		return self::$db->commit ();
	}

	/**
	 * Commits nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 */
	public function commitToLevel($transactionLevel) {
		return self::$db->commitToLevel ( $transactionLevel );
	}

	/**
	 * Commits all nested transactions (up to level 0)
	 */
	public function commitAll() {
		return self::$db->commitAll ();
	}

	/**
	 * Rolls back a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function rollBack() {
		return self::$db->rollBack ();
	}

	/**
	 * Rolls back nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 */
	public function rollBackToLevel($transactionLevel) {
		return self::$db->rollBackToLevel ( $transactionLevel );
	}

	/**
	 * Rolls back all nested transactions (up to level 0)
	 */
	public function rollBackAll() {
		return self::$db->rollBackAll ();
	}

	/**
	 * Call a callback with an array of parameters in a transaction
	 *
	 * @param callable $callback
	 * @param mixed ...$parameters
	 * @throws \Exception
	 * @return mixed
	 */
	function callInTransaction($callback, ...$parameters) {
		return self::$db->callInTransaction ( $callback, ...$parameters );
	}
}

