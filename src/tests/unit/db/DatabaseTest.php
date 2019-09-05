<?php
use Ubiquity\db\Database;
use Ubiquity\cache\database\TableCache;
use Ubiquity\exceptions\CacheException;
use Ubiquity\exceptions\DBException;
use Ubiquity\db\SqlUtils;
use Ubiquity\db\providers\pdo\PDOWrapper;
use Ubiquity\orm\DAO;

/**
 * Database test case.
 *
 * @covers \Ubiquity\db\Database
 *
 */
class DatabaseTest extends BaseTest {
	protected $dbType;
	protected $dbName;
	/**
	 *
	 * @var Database
	 */
	private $database;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$db = DAO::getDbOffset ( $this->config );
		$this->dbType = $db ['type'];
		$this->dbName = $db ['dbName'];
		$this->database = new Database ( $this->getWrapper (), $this->dbType, $this->dbName, $this->db_server );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->database = null;
	}

	protected function getWrapper() {
		return PDOWrapper::class;
	}

	protected function beforeQuery() {
		if (! $this->database->isConnected ())
			$this->database->connect ();
	}

	/**
	 * Tests Database->__construct()
	 *
	 * @covers \Ubiquity\db\Database::<private>
	 */
	public function test__construct() {
		$this->assertEquals ( $this->dbName, $this->database->getDbName () );
		$this->assertEquals ( '3306', $this->database->getPort () );
		$db = new Database ( $this->getWrapper (), $this->dbType, $this->dbName, $this->db_server, 3306, 'root', '', [ "quote" => "`" ], TableCache::class );
		$this->assertTrue ( $db->connect () );
		$db = new Database ( $this->getWrapper (), $this->dbType, $this->dbName, $this->db_server, 3306, 'root', '', [ "quote" => "`" ], function () {
			return new TableCache ();
		} );
		$this->assertTrue ( $db->connect () );
		$this->expectException ( CacheException::class );
		$db = new Database ( $this->getWrapper (), $this->dbType, $this->dbName, $this->db_server, 3306, 'root', '', [ "quote" => "`" ], "notExistingClass" );
		$this->assertTrue ( $db->connect () );
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
		$this->database->setUser ( 'nobody' );
		$this->expectException ( DBException::class );
		$this->database->connect ();
	}

	/**
	 * Tests Database->setters
	 */
	public function testSetters() {
		$this->database->setDbName ( 'test' );
		$this->assertEquals ( 'test', $this->database->getDbName () );
		$this->database->setDbType ( 'mongo' );
		$this->assertEquals ( 'mongo', $this->database->getDbType () );
		$options = [ "a" => true,"b" => "test" ];
		$this->database->setOptions ( $options );
		$this->assertEquals ( $options, $this->database->getOptions () );
		$this->database->setPassword ( 'password' );
		$this->assertEquals ( 'password', $this->database->getPassword () );
		$this->database->setPort ( 3307 );
		$this->assertEquals ( 3307, $this->database->getPort () );
		$this->database->setServerName ( 'local' );
		$this->assertEquals ( 'local', $this->database->getServerName () );
		$this->database->setUser ( 'user' );
		$this->assertEquals ( 'user', $this->database->getUser () );
	}

	/**
	 * Tests Database->getDSN()
	 */
	public function testGetDSN() {
		$db = new Database ( $this->getWrapper (), $this->dbType, "dbname" );
		$dsn = $db->getDSN ();
		$this->assertEquals ( $this->dbType . ':dbname=dbname;host=127.0.0.1;charset=UTF8;port=3306', $dsn );
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
		$st = $this->database->query ( "SELECT * from `User` limit 0,1" );
		$this->assertInstanceOf ( PDOStatement::class, $st );
	}

	/**
	 * Tests Database->prepareAndExecute()
	 */
	public function testPrepareAndExecute() {
		$this->beforeQuery ();
		$fields = SqlUtils::getFieldList ( [ "email","firstname" ], 'User' );
		$response = $this->database->prepareAndExecute ( "User", "WHERE `email`='benjamin.sherman@gmail.com'", $fields );
		$this->assertEquals ( sizeof ( $response ), 1 );
		$row = current ( $response );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row ['email'] );
		$this->assertEquals ( "Benjamin", $row ['firstname'] );
		$this->assertArrayNotHasKey ( 'lastname', $row );
		$this->expectException ( Error::class );
		try {
			$this->database->prepareAndExecute ( "users", "WHERE `email`='benjamin.sherman@gmail.com'", $fields, null, true );
		} catch ( Exception $e ) {
			// Nothing
		}
		$db = new Database ( $this->getWrapper (), $this->dbType, $this->dbName, $this->db_server, 3306, 'root', '', [ "quote" => "`" ], TableCache::class );
		$db->connect ();
		$response = $db->prepareAndExecute ( "User", "WHERE `email`='benjamin.sherman@gmail.com'", $fields, null, true );
		$this->assertEquals ( sizeof ( $response ), 1 );
		$row = current ( $response );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row ['email'] );
		$response = $db->prepareAndExecute ( "User", "WHERE `email`= ?", $fields, [ 'benjamin.sherman@gmail.com' ], true );
		$this->assertEquals ( sizeof ( $response ), 1 );
		$row = current ( $response );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row ['email'] );
	}

	/**
	 * Tests Database->prepareAndFetchAll()
	 */
	public function testPrepareAndFetchAll() {
		$this->beforeQuery ();
		$this->assertNotFalse ( $this->database->prepareAndFetchAll ( "SELECT 1" ) );
		$resp = $this->database->prepareAndFetchAll ( "select * from `User`" );
		$this->assertEquals ( 101, sizeof ( $resp ) );
		$row = current ( $resp );
		$this->assertEquals ( 7, sizeof ( $row ) );
		$resp = $this->database->prepareAndFetchAll ( "select * from `User` where email= ?", [ "benjamin.sherman@gmail.com" ] );
		$row = current ( $resp );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row ['email'] );
		$this->assertEquals ( "Benjamin", $row ['firstname'] );
		$resp = $this->database->prepareAndFetchAll ( "select * from `User` where email= ? and firstname= ?", [ "benjamin.sherman@gmail.com","Benjamin" ] );
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
		$resp = $this->database->prepareAndFetchAllColumn ( "select * from `User`" );
		$this->assertEquals ( 101, sizeof ( $resp ) );
		$resp = $this->database->prepareAndFetchAllColumn ( "select email from `User` where email= ?", [ "benjamin.sherman@gmail.com" ] );
		$row = current ( $resp );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row );
		$resp = $this->database->prepareAndFetchAllColumn ( "select * from `User` where email= ? and firstname= ?", [ "benjamin.sherman@gmail.com","Benjamin" ], 3 );
		$row = current ( $resp );
		$this->assertEquals ( "benjamin.sherman@gmail.com", $row );
	}

	/**
	 * Tests Database->prepareAndFetchColumn()
	 */
	public function testPrepareAndFetchColumn() {
		$this->beforeQuery ();
		$result = $this->database->prepareAndFetchColumn ( "select email from `User` where email='benjamin.sherman@gmail.com'" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select email from `User` limit 0,1" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select * from `User` limit 0,1", null, 3 );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select * from `User` where `email`= ?", [ 'benjamin.sherman@gmail.com' ], 3 );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->prepareAndFetchColumn ( "select email from `User` where `email`= ?", [ 'benjamin.sherman@gmail.com' ] );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
	}

	/**
	 * Tests Database->execute()
	 */
	public function testExecute() {
		$this->beforeQuery ();
		$this->assertEquals ( 0, $this->database->execute ( "DELETE FROM Organization where 1=2" ) );
		$this->assertEquals ( 1, $this->database->execute ( "INSERT INTO Organization(`name`,`domain`,`aliases`) VALUES('name','domain','aliases')" ) );
		$this->assertEquals ( 1, $this->database->execute ( "DELETE FROM Organization where `name`='name'" ) );
	}

	/**
	 * Tests Database->setServerName()
	 */
	public function testSetServerName() {
		$this->assertEquals ( $this->db_server, $this->database->getServerName () );
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
		$st = $this->database->prepareStatement ( "SELECT * from `User` limit 0,1" );
		$this->assertInstanceOf ( PDOStatement::class, $st );
	}

	/**
	 * Tests Database->bindValueFromStatement()
	 */
	public function testBindValueFromStatement() {
		$this->beforeQuery ();
		$st = $this->database->prepareStatement ( "SELECT * from `User` where email= :email limit 0,1" );
		$this->assertTrue ( $this->database->bindValueFromStatement ( $st, ':email', 'benjamin.sherman@gmail.com' ) );
	}

	/**
	 * Tests Database->lastInserId()
	 */
	public function testLastInserId() {
		$this->beforeQuery ();
		$this->assertEquals ( 1, $this->database->execute ( "INSERT INTO Organization(`name`,`domain`,`aliases`) VALUES('name','domain','aliases')" ) );
		$id = $this->database->lastInserId ();
		$this->assertNotNull ( $id );
		$this->assertEquals ( 1, $this->database->execute ( "DELETE FROM Organization where `id`=" . $id ) );
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
		$this->assertEquals ( 101, $this->database->count ( "User" ) );
		$this->assertEquals ( 1, $this->database->count ( "User", "`email`='benjamin.sherman@gmail.com'" ) );
		$this->assertEquals ( 0, $this->database->count ( "User", "1=2" ) );
		$this->assertEquals ( 101, $this->database->count ( "User", "1=1" ) );
	}

	/**
	 * Tests Database->queryColumn()
	 */
	public function testQueryColumn() {
		$this->beforeQuery ();
		$result = $this->database->queryColumn ( "select email from `User` where email='benjamin.sherman@gmail.com'" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->queryColumn ( "select email from `User` limit 0,1" );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
		$result = $this->database->queryColumn ( "select * from `User` limit 0,1", 3 );
		$this->assertEquals ( 'benjamin.sherman@gmail.com', $result );
	}

	/**
	 * Tests Database->fetchAll()
	 */
	public function testFetchAll() {
		$this->beforeQuery ();
		$result = $this->database->fetchAll ( "select * from `User`" );
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
		$this->assertEquals ( $this->dbType, $this->database->getDbType () );
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
	 * Tests Database->getDbObject()
	 */
	public function testGetDbObject() {
		$this->assertNull ( $this->database->getDbObject () );
		$this->beforeQuery ();
		$pdoo = $this->database->getDbObject ();
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

