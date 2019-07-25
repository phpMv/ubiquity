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

/**
 * Ubiquity PDO database class.
 * Ubiquity\db$Database
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 *
 */
class Database {
	use DatabaseOperationsTrait,DatabaseTransactionsTrait;
	private $dbType;
	private $serverName;
	private $port;
	private $dbName;
	private $user;
	private $password;
	private $cache;
	private $options;

	/**
	 *
	 * @var \PDO
	 */
	protected $pdoObject;

	/**
	 * Constructor
	 *
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param array $options
	 * @param boolean|string $cache
	 */
	public function __construct($dbType, $dbName, $serverName = "127.0.0.1", $port = "3306", $user = "root", $password = "", $options = [], $cache = false) {
		$this->dbType = $dbType;
		$this->dbName = $dbName;
		$this->serverName = $serverName;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		if (isset ( $options ["quote"] ))
			SqlUtils::$quote = $options ["quote"];
		$this->options = $options;
		if ($cache !== false) {
			if ($cache instanceof \Closure) {
				$this->cache = $cache ();
			} else {
				if (\class_exists ( $cache )) {
					$this->cache = new $cache ();
				} else {
					throw new CacheException ( $cache . " is not a valid value for database cache" );
				}
			}
		}
	}

	/**
	 * Creates the PDO instance and realize a safe connection.
	 *
	 * @throws DBException
	 * @return boolean
	 */
	public function connect() {
		try {
			$this->_connect ();
			return true;
		} catch ( \PDOException $e ) {
			throw new DBException ( $e->getMessage (), $e->getCode (), $e->getPrevious () );
		}
	}

	public function getDSN() {
		return $this->dbType . ':dbname=' . $this->dbName . ';host=' . $this->serverName . ';charset=UTF8;port=' . $this->port;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getServerName() {
		return $this->serverName;
	}

	public function setServerName($serverName) {
		$this->serverName = $serverName;
	}

	public function setDbType($dbType) {
		$this->dbType = $dbType;
		return $this;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getDbName() {
		return $this->dbName;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getUser() {
		return $this->user;
	}

	public static function getAvailableDrivers() {
		return \PDO::getAvailableDrivers ();
	}

	/**
	 *
	 * @return mixed
	 * @codeCoverageIgnore
	 */
	public function getDbType() {
		return $this->dbType;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 *
	 * @param string $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 *
	 * @param string $dbName
	 */
	public function setDbName($dbName) {
		$this->dbName = $dbName;
	}

	/**
	 *
	 * @param string $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 *
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 *
	 * @param array $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * Closes the active pdo connection
	 */
	public function close() {
		$this->pdoObject = null;
	}

	/**
	 * Starts and returns a database instance corresponding to an offset in config
	 *
	 * @param string $offset
	 * @return \Ubiquity\db\Database|NULL
	 */
	public static function start($offset = null) {
		$config = Startup::$config;
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ?? [ ]);
		if ($db ['dbName'] !== '') {
			$database = new Database ( $db ['type'], $db ['dbName'], $db ['serverName'] ?? '127.0.0.1', $db ['port'] ?? 3306, $db ['user'] ?? 'root', $db ['password'] ?? '', $db ['options'] ?? [ ], $db ['cache'] ?? false);
			$database->connect ();
			return $database;
		}
		return null;
	}
}
