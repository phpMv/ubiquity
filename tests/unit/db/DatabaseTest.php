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
		$this->db_server = getenv ( 'SERVICE_MYSQL_IP' ) ?? '127.0.0.1';
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
		$this->assertTrue ( $this->database->connect () );
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
		// TODO Auto-generated DatabaseTest->testPrepareAndFetchAllColumn()
		$this->markTestIncomplete ( "prepareAndFetchAllColumn test not implemented" );

		$this->database->prepareAndFetchAllColumn(/* parameters */);
	}

	/**
	 * Tests Database->prepareAndFetchColumn()
	 */
	public function testPrepareAndFetchColumn() {
		// TODO Auto-generated DatabaseTest->testPrepareAndFetchColumn()
		$this->markTestIncomplete ( "prepareAndFetchColumn test not implemented" );

		$this->database->prepareAndFetchColumn(/* parameters */);
	}

	/**
	 * Tests Database->execute()
	 */
	public function testExecute() {
		// TODO Auto-generated DatabaseTest->testExecute()
		$this->markTestIncomplete ( "execute test not implemented" );

		$this->database->execute(/* parameters */);
	}

	/**
	 * Tests Database->getServerName()
	 */
	public function testGetServerName() {
		// TODO Auto-generated DatabaseTest->testGetServerName()
		$this->markTestIncomplete ( "getServerName test not implemented" );

		$this->database->getServerName(/* parameters */);
	}

	/**
	 * Tests Database->setServerName()
	 */
	public function testSetServerName() {
		// TODO Auto-generated DatabaseTest->testSetServerName()
		$this->markTestIncomplete ( "setServerName test not implemented" );

		$this->database->setServerName(/* parameters */);
	}

	/**
	 * Tests Database->prepareStatement()
	 */
	public function testPrepareStatement() {
		// TODO Auto-generated DatabaseTest->testPrepareStatement()
		$this->markTestIncomplete ( "prepareStatement test not implemented" );

		$this->database->prepareStatement(/* parameters */);
	}

	/**
	 * Tests Database->bindValueFromStatement()
	 */
	public function testBindValueFromStatement() {
		// TODO Auto-generated DatabaseTest->testBindValueFromStatement()
		$this->markTestIncomplete ( "bindValueFromStatement test not implemented" );

		$this->database->bindValueFromStatement(/* parameters */);
	}

	/**
	 * Tests Database->lastInserId()
	 */
	public function testLastInserId() {
		// TODO Auto-generated DatabaseTest->testLastInserId()
		$this->markTestIncomplete ( "lastInserId test not implemented" );

		$this->database->lastInserId(/* parameters */);
	}

	/**
	 * Tests Database->getTablesName()
	 */
	public function testGetTablesName() {
		// TODO Auto-generated DatabaseTest->testGetTablesName()
		$this->markTestIncomplete ( "getTablesName test not implemented" );

		$this->database->getTablesName(/* parameters */);
	}

	/**
	 * Tests Database->count()
	 */
	public function testCount() {
		$this->beforeQuery ();
		$this->assertEquals ( 101, $this->database->count ( "user" ) );
		$this->assertEquals ( 1, $this->database->count ( "user", "`email`='benjamin.sherman@gmail.com'" ) );
		$this->assertEquals ( 0, $this->database->count ( "user", "1=2" ) );
	}

	/**
	 * Tests Database->queryColumn()
	 */
	public function testQueryColumn() {
		// TODO Auto-generated DatabaseTest->testQueryColumn()
		$this->markTestIncomplete ( "queryColumn test not implemented" );

		$this->database->queryColumn(/* parameters */);
	}

	/**
	 * Tests Database->fetchAll()
	 */
	public function testFetchAll() {
		// TODO Auto-generated DatabaseTest->testFetchAll()
		$this->markTestIncomplete ( "fetchAll test not implemented" );

		$this->database->fetchAll(/* parameters */);
	}

	/**
	 * Tests Database->isConnected()
	 */
	public function testIsConnected() {
		// TODO Auto-generated DatabaseTest->testIsConnected()
		$this->markTestIncomplete ( "isConnected test not implemented" );

		$this->database->isConnected(/* parameters */);
	}

	/**
	 * Tests Database->setDbType()
	 */
	public function testSetDbType() {
		// TODO Auto-generated DatabaseTest->testSetDbType()
		$this->markTestIncomplete ( "setDbType test not implemented" );

		$this->database->setDbType(/* parameters */);
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
	 * Tests Database->getPort()
	 */
	public function testGetPort() {
		// TODO Auto-generated DatabaseTest->testGetPort()
		$this->markTestIncomplete ( "getPort test not implemented" );

		$this->database->getPort(/* parameters */);
	}

	/**
	 * Tests Database->getDbName()
	 */
	public function testGetDbName() {
		// TODO Auto-generated DatabaseTest->testGetDbName()
		$this->markTestIncomplete ( "getDbName test not implemented" );

		$this->database->getDbName(/* parameters */);
	}

	/**
	 * Tests Database->getUser()
	 */
	public function testGetUser() {
		// TODO Auto-generated DatabaseTest->testGetUser()
		$this->markTestIncomplete ( "getUser test not implemented" );

		$this->database->getUser(/* parameters */);
	}

	/**
	 * Tests Database->getPdoObject()
	 */
	public function testGetPdoObject() {
		// TODO Auto-generated DatabaseTest->testGetPdoObject()
		$this->markTestIncomplete ( "getPdoObject test not implemented" );

		$this->database->getPdoObject(/* parameters */);
	}

	/**
	 * Tests Database::getAvailableDrivers()
	 */
	public function testGetAvailableDrivers() {
		$drivers = Database::getAvailableDrivers ();
		$this->assertEquals ( 2, sizeof ( $drivers ) );
	}
}

