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
	 * @param string $offset the database offset to use
	 * @return boolean true on success or false on failure
	 */
	public static function beginTransaction($offset = 'default') {
		return self::$db [$offset]->beginTransaction ();
	}

	/**
	 * Commits a transaction
	 *
	 * @param string $offset the database offset to use
	 * @return boolean true on success or false on failure
	 */
	public static function commit($offset = 'default') {
		return self::$db [$offset]->commit ();
	}

	/**
	 * Commits nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 * @param string $offset the database offset to use
	 * @return boolean true on success or false on failure
	 */
	public static function commitToLevel($transactionLevel, $offset = 'default') {
		return self::$db [$offset]->commitToLevel ( $transactionLevel );
	}

	/**
	 * Commits all nested transactions (up to level 0)
	 *
	 * @param string $offset the database offset to use
	 *
	 * @return boolean true on success or false on failure
	 */
	public static function commitAll($offset = 'default') {
		return self::$db [$offset]->commitAll ();
	}

	/**
	 * Rolls back a transaction
	 *
	 * @param string $offset the database offset to use
	 * @return boolean true on success or false on failure
	 */
	public static function rollBack($offset = 'default') {
		return self::$db [$offset]->rollBack ();
	}

	/**
	 * Rolls back nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 * @param string $offset the database offset to use
	 * @return boolean true on success or false on failure
	 */
	public static function rollBackToLevel($transactionLevel, $offset = 'default') {
		return self::$db [$offset]->rollBackToLevel ( $transactionLevel );
	}

	/**
	 * Rolls back all nested transactions (up to level 0)
	 *
	 * @param string $offset the database offset to use
	 * @return boolean true on success or false on failure
	 */
	public static function rollBackAll($offset = 'default') {
		return self::$db [$offset]->rollBackAll ();
	}

	/**
	 * Call a callback with an array of parameters in a transaction
	 *
	 * @param callable $callback
	 * @param string $offset the database offset to use
	 * @param mixed ...$parameters
	 * @throws \Exception
	 * @return mixed
	 */
	public static function callInTransaction($callback, $offset, ...$parameters) {
		return self::$db->callInTransaction ( $callback, $offset, ...$parameters );
	}
}

