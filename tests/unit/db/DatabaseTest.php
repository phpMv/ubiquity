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
	const DB_NAME = "messagerie";
	const DB_SERVER = "172.18.0.2";

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		$this->database = new Database ( "mysql", self::DB_NAME, self::DB_SERVER );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->database = null;
	}

	/**
	 * Tests Database->__construct()
	 */
	public function test__construct() {
		// TODO Auto-generated DatabaseTest->test__construct()
		$this->markTestIncomplete ( "__construct test not implemented" );

		$this->database->__construct(/* parameters */);
	}

	/**
	 * Tests Database->connect()
	 */
	public function testConnect() {
		$this->assertTrue ( $this->database->connect () );
	}

	/**
	 * Tests Database->_connect()
	 */
	public function test_connect() {
		// TODO Auto-generated DatabaseTest->test_connect()
		$this->markTestIncomplete ( "_connect test not implemented" );

		$this->database->_connect(/* parameters */);
	}

	/**
	 * Tests Database->getDSN()
	 */
	public function testGetDSN() {
		// TODO Auto-generated DatabaseTest->testGetDSN()
		$this->markTestIncomplete ( "getDSN test not implemented" );

		$this->database->getDSN(/* parameters */);
	}

	/**
	 * Tests Database->query()
	 */
	public function testQuery() {
		// TODO Auto-generated DatabaseTest->testQuery()
		$this->markTestIncomplete ( "query test not implemented" );

		$this->database->query(/* parameters */);
	}

	/**
	 * Tests Database->prepareAndExecute()
	 */
	public function testPrepareAndExecute() {
		// TODO Auto-generated DatabaseTest->testPrepareAndExecute()
		$this->markTestIncomplete ( "prepareAndExecute test not implemented" );

		$this->database->prepareAndExecute(/* parameters */);
	}

	/**
	 * Tests Database->prepareAndFetchAll()
	 */
	public function testPrepareAndFetchAll() {
		// TODO Auto-generated DatabaseTest->testPrepareAndFetchAll()
		$this->markTestIncomplete ( "prepareAndFetchAll test not implemented" );

		$this->database->prepareAndFetchAll(/* parameters */);
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
		// TODO Auto-generated DatabaseTest->testCount()
		$this->markTestIncomplete ( "count test not implemented" );

		$this->database->count(/* parameters */);
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
		// TODO Auto-generated DatabaseTest->testPing()
		$this->markTestIncomplete ( "ping test not implemented" );

		$this->database->ping(/* parameters */);
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
		// TODO Auto-generated DatabaseTest::testGetAvailableDrivers()
		$this->markTestIncomplete ( "getAvailableDrivers test not implemented" );

		Database::getAvailableDrivers(/* parameters */);
	}
}

