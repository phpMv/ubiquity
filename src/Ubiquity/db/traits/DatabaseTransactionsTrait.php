<?php

namespace Ubiquity\db\traits;

use Ubiquity\exceptions\DBException;

/**
 * Manages database transactions.
 * Ubiquity\db\traits$DatabaseTransactionsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @property \PDO $pdoObject
 * @property string $dbType
 */
trait DatabaseTransactionsTrait {
	protected static $savepointsDrivers = [ 'pgsql','mysql','sqlite' ];
	protected $transactionLevel = 0;

	protected function nestable() {
		return isset ( self::$savepointsDrivers [$this->dbType] );
	}

	/**
	 * Initiates a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function beginTransaction() {
		if ($this->transactionLevel == 0 || ! $this->nestable ()) {
			$ret = $this->pdoObject->beginTransaction ();
			$this->transactionLevel ++;
			return $ret;
		}
		$this->pdoObject->exec ( 'SAVEPOINT LEVEL' . $this->transactionLevel );
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
			return $this->pdoObject->commit ();
		}
		$this->pdoObject->exec ( 'RELEASE SAVEPOINT LEVEL' . $this->transactionLevel );
		return true;
	}

	/**
	 * Commits nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 */
	public function commitToLevel($transactionLevel) {
		while ( $this->transactionLevel > $transactionLevel ) {
			$this->commit ();
		}
	}

	/**
	 * Commits all nested transactions (up to level 0)
	 */
	public function commitAll() {
		$this->commitToLevel ( 0 );
	}

	/**
	 * Rolls back a transaction
	 *
	 * @return boolean true on success or false on failure
	 */
	public function rollBack() {
		$this->transactionLevel --;

		if ($this->transactionLevel == 0 || ! $this->nestable ()) {
			return $this->pdoObject->rollBack ();
		}
		$this->pdoObject->exec ( 'ROLLBACK TO SAVEPOINT LEVEL' . $this->transactionLevel );
		return true;
	}

	/**
	 * Rolls back nested transactions up to level $transactionLevel
	 *
	 * @param int $transactionLevel
	 */
	public function rollBackToLevel($transactionLevel) {
		while ( $this->transactionLevel > $transactionLevel ) {
			$this->rollBack ();
		}
	}

	/**
	 * Rolls back all nested transactions (up to level 0)
	 */
	public function rollBackAll() {
		$this->rollBackToLevel ( 0 );
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
		if ($this->beginTransaction ()) {
			try {
				$ret = call_user_func_array ( $callback, $parameters );
			} catch ( \Exception $e ) {
				$this->rollBack ();
				throw $e;
			}

			if ($ret) {
				if (! $this->commit ())
					throw new DBException ( 'Transaction was not committed.' );
			} else {
				$this->rollBack ();
			}

			return $ret;
		}
		throw new DBException ( 'Transaction was not started.' );
	}
}
