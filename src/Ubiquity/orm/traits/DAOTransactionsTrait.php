<?php

namespace Ubiquity\orm\traits;

/**
 * Adds transactions in DAO class.
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
	public static function beginTransaction() {
		return self::$db->beginTransaction ();
	}

	/**
	 * Commits a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public static function commit() {
		return self::$db->commit ();
	}

	/**
	 * Commits nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 * @return boolean true on success or false on failure
	 */
	public static function commitToLevel($transactionLevel) {
		return self::$db->commitToLevel ( $transactionLevel );
	}

	/**
	 * Commits all nested transactions (up to level 0)
	 *
	 * @return boolean true on success or false on failure
	 */
	public static function commitAll() {
		return self::$db->commitAll ();
	}

	/**
	 * Rolls back a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public static function rollBack() {
		return self::$db->rollBack ();
	}

	/**
	 * Rolls back nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 * @return boolean true on success or false on failure
	 */
	public static function rollBackToLevel($transactionLevel) {
		return self::$db->rollBackToLevel ( $transactionLevel );
	}

	/**
	 * Rolls back all nested transactions (up to level 0)
	 *
	 * @return boolean true on success or false on failure
	 */
	public static function rollBackAll() {
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
	public static function callInTransaction($callback, ...$parameters) {
		return self::$db->callInTransaction ( $callback, ...$parameters );
	}
}

