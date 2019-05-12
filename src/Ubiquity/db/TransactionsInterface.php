<?php

namespace Ubiquity\db;

/**
 * Defines transaction methods.
 * Ubiquity\db$TransactionsInterface
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
interface TransactionsInterface {

	/**
	 * Initiates a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function beginTransaction();

	/**
	 * Commits a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function commit();

	/**
	 * Commits nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 */
	public function commitToLevel($transactionLevel);

	/**
	 * Commits all nested transactions (up to level 0)
	 */
	public function commitAll();

	/**
	 * Rolls back a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function rollBack();

	/**
	 * Rolls back nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 */
	public function rollBackToLevel($transactionLevel);

	/**
	 * Rolls back all nested transactions (up to level 0)
	 */
	public function rollBackAll();

	/**
	 * Call a callback with an array of parameters in a transaction
	 *
	 * @param callable $callback
	 * @param mixed ...$parameters
	 * @throws \Exception
	 * @return mixed
	 */
	function callInTransaction($callback, ...$parameters);
}

