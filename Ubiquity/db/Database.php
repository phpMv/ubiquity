<?php

namespace Ubiquity\db;

use Ubiquity\cache\database\DbCache;
use Ubiquity\exceptions\CacheException;

/**
 * Classe d'accès aux Bases de données encapsulant un objet PDO
 * @author heron
 * @version 1.0.0.3
 * @package db
 *
 */
class Database {
	private $dbType;
	private $serverName;
	private $port;
	private $dbName;
	private $user;
	private $password;
	private $pdoObject;
	private $statements=[ ];
	private $cache;
	private $options;

	/**
	 * Constructeur
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param array $options
	 * @param boolean|string $cache
	 */
	public function __construct($dbType,$dbName, $serverName="localhost", $port="3306", $user="root", $password="", $options=[],$cache=false) {
		$this->dbType=$dbType;
		$this->dbName=$dbName;
		$this->serverName=$serverName;
		$this->port=$port;
		$this->user=$user;
		$this->password=$password;
		$this->options=$options;
		if ($cache !== false) {
			if(\is_callable($cache)){
				$this->cache=$cache();
			}else{
				if(\class_exists($cache)){
					$this->cache=new $cache();
				}else{
					throw new CacheException($cache." is not a valid value for database cache");
				}
			}
		}
	}

	/**
	 * Réalise la connexion à la base de données
	 */
	public function connect() {
		$this->pdoObject=new \PDO($this->dbType.':host=' . $this->serverName . ';dbname=' . $this->dbName . ';charset=UTF8;port:' . $this->port, $this->user, $this->password,$this->options);
		$this->pdoObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Exécute l'instruction SQL passée en paramètre et retourne un statement
	 * @param string $sql
	 * @return PDOStatement
	 */
	public function query($sql) {
		return $this->pdoObject->query($sql);
	}

	public function prepareAndExecute($tableName, $condition,$fields,$useCache=NULL) {
		$cache=(DbCache::$active && $useCache !== false) || (!DbCache::$active && $useCache === true);
		$result=false;
		if ($cache) {
			$result=$this->cache->fetch($tableName, $condition);
		}
		if ($result === false) {
			$fields=SqlUtils::getFieldList($fields);
			$statement=$this->getStatement("SELECT {$fields} FROM " . $tableName . $condition);
			$statement->execute();
			$result=$statement->fetchAll();
			$statement->closeCursor();
			if ($cache) {
				$this->cache->store($tableName, $condition, $result);
			}
		}
		return $result;
	}

	private function getStatement($sql) {
		if (!isset($this->statements[$sql])) {
			$this->statements[$sql]=$this->pdoObject->prepare($sql);
			$this->statements[$sql]->setFetchMode(\PDO::FETCH_ASSOC);
		}
		return $this->statements[$sql];
	}

	/**
	 * Exécute l'instruction sql $sql de mise à jour (INSERT, UPDATE ou DELETE)
	 * @return le nombre d'enregistrements affectés
	 */
	public function execute($sql) {
		return $this->pdoObject->exec($sql);
	}

	public function getServerName() {
		return $this->serverName;
	}

	public function setServerName($serverName) {
		$this->serverName=$serverName;
	}

	/**
	 * Prépare l'instruction $sql pour son exécution
	 * @param String $sql
	 * @return PDOStatement
	 */
	public function prepareStatement($sql) {
		return $this->pdoObject->prepare($sql);
	}

	/**
	 * Sets $value to $parameter
	 * @param PDOStatement $statement
	 * @param String $parameter
	 * @param mixed $value
	 * @return boolean
	 */
	public function bindValueFromStatement(\PDOStatement $statement, $parameter, $value) {
		return $statement->bindValue(":" . $parameter, $value);
	}

	/**
	 * Returns the last insert id
	 * @return integer
	 */
	public function lastInserId() {
		return $this->pdoObject->lastInsertId();
	}

	public function getTablesName() {
		$sql='SHOW TABLES';
		$query=$this->pdoObject->query($sql);
		return $query->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * Retourne le nombre d'enregistrements de $tableName respectant la condition éventuellement passée en paramètre
	 * @param string $tableName
	 * @param string $condition Partie suivant le WHERE d'une instruction SQL
	 */
	public function count($tableName, $condition='') {
		if ($condition != '')
			$condition=" WHERE " . $condition;
			return $this->query("SELECT COUNT(*) FROM " . $tableName . $condition)->fetchColumn();
	}

	public function isConnected(){
		return ($this->pdoObject!==null && $this->pdoObject instanceof \PDO);
	}

	public function setDbType($dbType) {
		$this->dbType=$dbType;
		return $this;
	}

	public function getPort() {
		return $this->port;
	}

	public function getDbName() {
		return $this->dbName;
	}

	public function getUser() {
		return $this->user;
	}

	public function getPdoObject() {
		return $this->pdoObject;
	}

}
