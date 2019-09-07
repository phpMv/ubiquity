<?php

namespace Ubiquity\db\traits;

use Ubiquity\exceptions\DBException;
use Ubiquity\log\Logger;

/**
 * Manages database transactions.
 * Ubiquity\db\traits$DatabaseTransactionsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.0
 * @property \Ubiquity\db\providers\AbstractDbWrapper $wrapperObject
 * @property string $dbType
 */
trait DatabaseTransactionsTrait {
	protected $transactionLevel = 0;

	protected function nestable() {
		return $this->wrapperObject->nestable ();
	}

	/**
	 * Initiates a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function beginTransaction() {
		if ($this->transactionLevel == 0 || ! $this->nestable ()) {
			$ret = $this->wrapperObject->beginTransaction ();
			Logger::info ( 'Transactions', 'Start transaction', 'beginTransaction' );
			$this->transactionLevel ++;
			return $ret;
		}
		$this->wrapperObject->savePoint ( $this->transactionLevel );
		Logger::info ( 'Transactions', 'Savepoint level', 'beginTransaction', $this->transactionLevel );
		$this->transactionLevel ++;
		return true;
	}

	/**
	 * Commits a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function commit() {
		$this->transactionLevel --;
		if ($this->transactionLevel == 0 || ! $this->nestable ()) {
			Logger::info ( 'Transactions', 'Commit transaction', 'commit' );
			return $this->wrapperObject->commit ();
		}
		$this->wrapperObject->releasePoint ( $this->transactionLevel );
		Logger::info ( 'Transactions', 'Release savepoint level', 'commit', $this->transactionLevel );
		return true;
	}

	/**
	 * Commits nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 * @return boolean true on success or false on failure
	 */
	public function commitToLevel($transactionLevel) {
		$res = true;
		while ( $res && $this->transactionLevel > $transactionLevel ) {
			$res = $this->commit ();
		}
		return $res;
	}

	/**
	 * Commits all nested transactions (up to level 0)
	 *
	 * @return boolean true on success or false on failure
	 */
	public function commitAll() {
		return $this->commitToLevel ( 0 );
	}

	/**
	 * Rolls back a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function rollBack() {
		$this->transactionLevel --;
		if ($this->transactionLevel == 0 || ! $this->nestable ()) {
			Logger::info ( 'Transactions', 'Rollback transaction', 'rollBack' );
			return $this->wrapperObject->rollBack ();
		}
		$this->wrapperObject->rollbackPoint ( $this->transactionLevel );
		Logger::info ( 'Transactions', 'Rollback to savepoint level', 'rollBack', $this->transactionLevel );
		return true;
	}

	/**
	 * Rolls back nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 * @return boolean true on success or false on failure
	 */
	public function rollBackToLevel($transactionLevel) {
		$res = true;
		while ( $res && $this->transactionLevel > $transactionLevel ) {
			$res = $this->rollBack ();
		}
		return $res;
	}

	/**
	 * Rolls back all nested transactions (up to level 0)
	 *
	 * @return boolean true on success or false on failure
	 */
	public function rollBackAll() {
		return $this->rollBackToLevel ( 0 );
	}

	/**
	 * Checks if inside a transaction
	 *
	 * @return boolean
	 */
	public function inTransaction() {
		return $this->wrapperObject->inTransaction ();
	}

	/**
	 * Call a callback with an array of parameters in a transaction
	 *
	 * @param callable $callback
	 * @param mixed ...$parameters
	 * @throws \Exception
	 * @return mixed
	 */
	public function callInTransaction($callback, ...$parameters) {
		if ($this->beginTransaction ()) {
			try {
				$ret = call_user_func_array ( $callback, $parameters );
			} catch ( \Exception $e ) {
				$this->wrapperObject->rollBack ();
				throw $e;
			}

			if ($ret) {
				if (! $this->commit ()) {
					throw new DBException ( 'Transaction was not committed.' );
				}
			} else {
				$this->rollBack ();
			}

			return $ret;
		}
		throw new DBException ( 'Transaction was not started.' );
	}
}
