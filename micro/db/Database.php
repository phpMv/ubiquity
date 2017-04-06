<?php
namespace micro\db;
/**
 * Classe d'accès aux Bases de données encapsulant un objet PDO
 * @author heron
 * @version 1.0.0.3
 * @package db
 *
 */
class Database {
	private $serverName;
	private $port;
	private $dbName;
	private $user;
	private $password;
	private $pdoObject;
	private $statements=[];

	/**
	 * Constructeur
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 */
	public function __construct($dbName, $serverName = "localhost", $port = "3306",
			$user = "root", $password = "") {
		$this->dbName = $dbName;
		$this->serverName = $serverName;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
	}

	/**
	 * Réalise la connexion à la base de données
	 */
	public function connect() {
		try {
			$this->pdoObject = new \PDO(
					'mysql:host=' . $this->serverName . ';dbname='
							. $this->dbName . ';port:' . $this->port,
					$this->user, $this->password);
			$this->pdoObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->pdoObject->exec("SET CHARACTER SET utf8");

		} catch (\PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
		}
	}

	/**
	 * Exécute l'instruction SQL passée en paramètre et retourne un statement
	 * @param string $sql
	 * @return PDOStatement
	 */
	public function query($sql) {
		return $this->pdoObject->query($sql);
	}

	public function prepareAndExecute($sql){
		$statement=$this->getStatement($sql);
		$statement->execute();
		$result= $statement->fetchAll();
		$statement->closeCursor();
		return $result;
	}

	private function getStatement($sql){
		if(!isset($this->statements[$sql])){
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
		$this->serverName = $serverName;
	}

	/**
	 * Prépare l'instruction $sql pour son exécution
	 * @param String $sql
	 * @return PDOStatement
	 */
	public function prepareStatement($sql){
		return $this->pdoObject->prepare($sql);
	}

	/**
	 * Affecte la valeur $value au paramétre $parameter
	 * @param PDOStatement $statement
	 * @param String $parameter
	 * @param mixed $value
	 * @return boolean
	 */
	public function bindValueFromStatement(\PDOStatement $statement,$parameter,$value){
		return $statement->bindValue(":".$parameter, $value);
	}

	/**
	 * retourne le dernier auto-increment généré
	 * @return integer
	 */
	public function lastInserId(){
		return $this->pdoObject->lastInsertId();
	}

	public function getTablesName(){
		$sql = 'SHOW TABLES';
		$query = $this->pdoObject->query($sql);
		return $query->fetchAll(\PDO::FETCH_COLUMN);
	}

}
