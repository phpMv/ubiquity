<?php
use Ubiquity\db\Database;

require_once 'Ubiquity/db/Database.php';

/**
 * Database test case.
 */
class DatabaseTest extends \Codeception\Test\Unit {

	/**
	 *
	 * @var Database
	 */
	private $database;
	private $db_server;
	const DB_NAME = "messagerie";

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		$ip = getenv ( 'SERVICE_MYSQL_IP' );
		if ($ip === false) {
			$ip = '127.0.0.1';
		}
		$this->db_server = $ip;
		$this->database = new Database ( "mysql", self::DB_NAME, $this->db_server );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->database = null;
	}

	protected function beforeQuery() {
		if (! $this->database->isConnected ())
			$this->database->connect ();
	}

	/**
	 * Tests Database->__construct()
	 */
	public function test__construct() {
		$this->assertEquals ( self::DB_NAME, $this->database->getDbName () );
		$this->assertEquals ( '3306', $this->database->getPort () );
	}

	/**
	 * Tests Database->connect()
	 */
	public function testConnect() {
		$this->assertFalse ( $this->database->isConnected () );
		$this->assertFalse ( $this->database->ping () );
		$this->assertTrue ( $this->database->connect () );
		$this->assertTrue ( $this->database->isConnected () );
		$this->assertTrue ( $this->database->ping () );
	}

	/**
	 * Tests Database->getDSN()
	 */
	public function testGetDSN() {
		$db = new Database ( "mysql", "dbname" );
		$dsn = $db->getDSN ();
		$this->assertEquals ( 'mysql:dbname=dbname;host=127.0.0.1;charset=UTF8;port=3306', $dsn );
		$db->setDbType ( "mongo" );
		$this->assertEquals ( 'mongo:dbname=dbname;host=127.0.0.1;charset=UTF8;port=3306', $db->getDSN () );
		$db->setServerName ( "localhost" );
		$this->assertEquals ( 'mongo:dbname=dbname;host=localhost;charset=UTF8;port=3306', $db->getDSN () );
		$db->setPort ( 23 );
		$this->assertEquals ( 'mongo:dbname=dbname;host=localhost;charset=UTF8;port=23', $db->getDSN () );
	}

	/**
	 * Tests Database->query()
	 */
	public function testQuery() {
		$this->beforeQuery ();
		$this->assertNotFalse ( $this->database->query ( "SELECT 1" ) );
		$st = $this->database->query ( "SELECT * from `user` limit 0,1" );
		$this->assertInstanceOf ( PDOStatement::class, $st );
	}

	/**
	 * Tests Database->prepareAndExecute()
	 */
	public function testPrepareAndExecute() {
		$this->beforeQuery ();
		$response = $this->database->prepareAndExecute ( "user", "WHERE `email`='benjamin.sherman@gmail.com'", [ "email","firstname" ] );
		$this->assertEquals ( sizeof ( $response ), 1 );
		$row = current ( $response );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row ['email'] );
		$this->assertEquals ( "Benjamin", $row ['firstname'] );
		$this->assertArrayNotHasKey ( 'lastname', $row );
	}

	/**
	 * Tests Database->prepareAndFetchAll()
	 */
	public function testPrepareAndFetchAll() {
		$this->beforeQuery ();
		$this->assertNotFalse ( $this->database->prepareAndFetchAll ( "SELECT 1" ) );
		$resp = $this->database->prepareAndFetchAll ( "select * from `user`" );
		$this->assertEquals ( 101, sizeof ( $resp ) );
		$row = current ( $resp );
		$this->assertEquals ( 7, sizeof ( $row ) );
		$resp = $this->database->prepareAndFetchAll ( "select * from `user` where email= ?", [ "benjamin.sherman@gmail.com" ] );
		$row = current ( $resp );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row ['email'] );
		$this->assertEquals ( "Benjamin", $row ['firstname'] );
		$resp = $this->database->prepareAndFetchAll ( "select * from `user` where email= ? and firstname= ?", [ "benjamin.sherman@gmail.com","Benjamin" ] );
		$row = current ( $resp );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row ['email'] );
		$this->assertEquals ( "Benjamin", $row ['firstname'] );
	}

	/**
	 * Tests Database->prepareAndFetchAllColumn()
	 */
	public function testPrepareAndFetchAllColumn() {
		$this->beforeQuery ();
		$this->assertNotFalse ( $this->database->prepareAndFetchAllColumn ( "SELECT 1" ) );
		$resp = $this->database->prepareAndFetchAllColumn ( "select * from `user`" );
		$this->assertEquals ( 101, sizeof ( $resp ) );
		$resp = $this->database->prepareAndFetchAllColumn ( "select email from `user` where email= ?", [ "benjamin.sherman@gmail.com" ] );
		$row = current ( $resp );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row );
		$resp = $this->database->prepareAndFetchAllColumn ( "select * from `user` where email= ? and firstname= ?", [ "benjamin.sherman@gmail.com","Benjamin" ], 3 );
		$row = current ( $resp );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row );
	}

	/**
	 * Tests Database->prepareAndFetchColumn()
	 */
	public function testPrepareAndFetchColumn() {
		$this->beforeQuery ();
		$result = $this->database->prepareAndFetchColumn ( "select email from `user` where email='benjamin.sherman@gmail.com'" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select email from `user` limit 0,1" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select * from `user` limit 0,1", null, 3 );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select * from `user` where `email`= ?", [ 'benjamin.sherman@gmail.com' ], 3 );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select email from `user` where `email`= ?", [ 'benjamin.sherman@gmail.com' ] );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
	}

	/**
	 * Tests Database->execute()
	 */
	public function testExecute() {
		$this->beforeQuery ();
		$this->assertEquals ( 0, $this->database->execute ( "DELETE FROM organization where 1=2" ) );
		$this->assertEquals ( 1, $this->database->execute ( "INSERT INTO organization(`name`,`domain`,`aliases`) VALUES('name','domain','aliases')" ) );
		$this->assertEquals ( 1, $this->database->execute ( "DELETE FROM organization where `name`='name'" ) );
	}

	/**
	 * Tests Database->setServerName()
	 */
	public function testSetServerName() {
		$this->assertEquals ( '127.0.0.1', $this->database->getServerName () );
		$this->database->setServerName ( 'localhost' );
		$this->assertEquals ( 'localhost', $this->database->getServerName () );
	}

	/**
	 * Tests Database->prepareStatement()
	 */
	public function testPrepareStatement() {
		$this->beforeQuery ();
		$st = $this->database->prepareStatement ( "SELECT 1" );
		$this->assertNotNull ( $st );
		$st = $this->database->prepareStatement ( "SELECT * from `user` limit 0,1" );
		$this->assertInstanceOf ( PDOStatement::class, $st );
	}

	/**
	 * Tests Database->bindValueFromStatement()
	 */
	public function testBindValueFromStatement() {
		$this->beforeQuery ();
		$st = $this->database->prepareStatement ( "SELECT * from `user` where email= :email limit 0,1" );
		$this->assertTrue ( $this->database->bindValueFromStatement ( $st, ':email', 'benjamin.sherman@gmail.com' ) );
	}

	/**
	 * Tests Database->lastInserId()
	 */
	public function testLastInserId() {
		$this->beforeQuery ();
		$this->assertEquals ( 1, $this->database->execute ( "INSERT INTO organization(`name`,`domain`,`aliases`) VALUES('name','domain','aliases')" ) );
		$id = $this->database->lastInserId ();
		$this->assertIsInt ( ( int ) $id );
		$this->assertEquals ( 1, $this->database->execute ( "DELETE FROM organization where `id`=" . $id ) );
	}

	/**
	 * Tests Database->getTablesName()
	 */
	public function testGetTablesName() {
		$this->beforeQuery ();
		$tables = $this->database->getTablesName ();
		$this->assertEquals ( 7, sizeof ( $tables ) );
	}

	/**
	 * Tests Database->count()
	 */
	public function testCount() {
		$this->beforeQuery ();
		$this->assertEquals ( 101, $this->database->count ( "user" ) );
		$this->assertEquals ( 1, $this->database->count ( "user", "`email`='benjamin.sherman@gmail.com'" ) );
		$this->assertEquals ( 0, $this->database->count ( "user", "1=2" ) );
		$this->assertEquals ( 101, $this->database->count ( "user", "1=1" ) );
	}

	/**
	 * Tests Database->queryColumn()
	 */
	public function testQueryColumn() {
		$this->beforeQuery ();
		$result = $this->database->queryColumn ( "select email from `user` where email='benjamin.sherman@gmail.com'" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->queryColumn ( "select email from `user` limit 0,1" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->queryColumn ( "select * from `user` limit 0,1", 3 );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
	}

	/**
	 * Tests Database->fetchAll()
	 */
	public function testFetchAll() {
		$this->beforeQuery ();
		$result = $this->database->fetchAll ( "select * from `user`" );
		$this->assertEquals ( 101, sizeof ( $result ) );
		$row = current ( $result );
		$this->assertEquals ( $row ['email'], 'benjamin.sherman@gmail.com' );
	}

	/**
	 * Tests Database->isConnected()
	 */
	public function testIsConnected() {
		$this->assertFalse ( $this->database->isConnected () );
		$this->assertFalse ( $this->database->ping () );
		$this->beforeQuery ();
		$this->assertTrue ( $this->database->isConnected () );
		$this->assertTrue ( $this->database->ping () );
	}

	/**
	 * Tests Database->setDbType()
	 */
	public function testSetDbType() {
		$this->assertEquals ( 'mysql', $this->database->getDbType () );
		$this->database->setDbType ( 'mongo' );
		$this->assertEquals ( 'mongo', $this->database->getDbType () );
	}

	/**
	 * Tests Database->ping()
	 */
	public function testPing() {
		$this->assertFalse ( $this->database->ping () );
		$this->beforeQuery ();
		$this->assertTrue ( $this->database->ping () );
	}

	/**
	 * Tests Database->getPdoObject()
	 */
	public function testGetPdoObject() {
		$this->assertNull ( $this->database->getPdoObject () );
		$this->beforeQuery ();
		$pdoo = $this->database->getPdoObject ();
		$this->assertNotNull ( $pdoo );
		$this->assertInstanceOf ( PDO::class, $pdoo );
	}

	/**
	 * Tests Database::getAvailableDrivers()
	 */
	public function testGetAvailableDrivers() {
		$drivers = Database::getAvailableDrivers ();
		$this->assertTrue ( sizeof ( $drivers ) > 0 );
	}
}

