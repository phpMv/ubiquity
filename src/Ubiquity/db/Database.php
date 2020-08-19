<?php

/**
 * Database implementation
 */
namespace Ubiquity\db;

use Ubiquity\exceptions\CacheException;
use Ubiquity\db\traits\DatabaseOperationsTrait;
use Ubiquity\exceptions\DBException;
use Ubiquity\db\traits\DatabaseTransactionsTrait;
use Ubiquity\controllers\Startup;
use Ubiquity\db\traits\DatabaseMetadatas;
use Ubiquity\cache\database\DbCache;

/**
 * Ubiquity Generic database class.
 * Ubiquity\db$Database
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.4
 *
 */
class Database extends AbstractDatabase {
	use DatabaseOperationsTrait,DatabaseTransactionsTrait,DatabaseMetadatas;
	public static $wrappers = [ 'pdo' => \Ubiquity\db\providers\pdo\PDOWrapper::class,'tarantool' => '\Ubiquity\db\providers\tarantool\TarantoolWrapper','mysqli' => '\Ubiquity\db\providers\mysqli\MysqliWrapper','swoole' => '\Ubiquity\db\providers\swoole\SwooleWrapper' ];
	public $quote;

	protected function setDbWrapperClass($dbWrapperClass, $dbType) {
		$this->wrapperObject = new $dbWrapperClass ( $this->dbType = $dbType );
		$this->quote = $this->wrapperObject->quote;
	}

	/**
	 * Starts and returns a database instance corresponding to an offset in config
	 *
	 * @param string $offset
	 * @param array $config Ubiquity config file content
	 * @return \Ubiquity\db\Database|NULL
	 */
	public static function start(string $offset = null, ?array $config = null): ?self {
		$config ??= Startup::$config;
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ?? [ ]);
		if ($db ['dbName'] !== '') {
			$database = new Database ( $db ['wrapper'] ?? \Ubiquity\db\providers\pdo\PDOWrapper::class, $db ['type'], $db ['dbName'], $db ['serverName'] ?? '127.0.0.1', $db ['port'] ?? 3306, $db ['user'] ?? 'root', $db ['password'] ?? '', $db ['options'] ?? [ ], $db ['cache'] ?? false);
			$database->connect ();
			return $database;
		}
		return null;
	}

	public function quoteValue($value, $type = 2) {
		return $this->wrapperObject->quoteValue ( ( string ) $value, $type );
	}

	public function getUpdateFieldsKeyAndValues($keyAndValues, $fields) {
		$ret = array ();
		foreach ( $fields as $field ) {
			$ret [] = $this->quote . $field . $this->quote . ' = ' . $this->quoteValue ( $keyAndValues [$field] );
		}
		return \implode ( ',', $ret );
	}

	public function getInsertValues($keyAndValues) {
		$ret = array ();
		foreach ( $keyAndValues as $value ) {
			$ret [] = $this->quoteValue ( $value );
		}
		return \implode ( ',', $ret );
	}

	public function getCondition(array $keyValues, $separator = ' AND ') {
		$retArray = array ();
		foreach ( $keyValues as $key => $value ) {
			$retArray [] = $this->quote . $key . $this->quote . " = " . $this->quoteValue ( $value );
		}
		return \implode ( $separator, $retArray );
	}

	public function getSpecificSQL($key, ?array $params = null) {
		switch ($key) {
			case 'groupconcat' :
				return $this->wrapperObject->groupConcat ( $params [0], $params [1] ?? ',');
			case 'tostring' :
				return $this->wrapperObject->toStringOperator ();
		}
	}
}
