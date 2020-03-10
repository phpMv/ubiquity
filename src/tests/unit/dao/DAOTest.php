<?php
use Ubiquity\orm\DAO;
use models\User;
use models\Organization;
use Ubiquity\db\Database;
use models\Groupe;
use Ubiquity\controllers\Startup;

/**
 * DAO test case.
 */
class DAOTest extends BaseTest {

	/**
	 *
	 * @var DAO
	 */
	private $dao;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->dao = new DAO ();
		$this->_startCache ();
		$this->_startDatabase ( $this->dao );
		$this->dao->prepareGetById ( "orga", Organization::class );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao->closeDb ();
	}

	public function testGetPrepared() {
		$this->dao->getPrepared ( 'orga' )->addMember ( 'CONCAT(name,":",domain)', 'fullname' );
		$orga = $this->dao->executePrepared ( 'orga', 1 );
		$this->assertInstanceOf ( Organization::class, $orga );
		$this->assertEquals ( "Conservatoire National des Arts et Métiers:lecnam.net", $orga->fullname );
	}

	/**
	 * Tests DAO::getManyToOne()
	 */
	public function testGetManyToOne() {
		$user = $this->dao->getOne ( User::class, "email='benjamin.sherman@gmail.com'", false );
		$orga = DAO::getManyToOne ( $user, 'organization' );
		$this->assertInstanceOf ( Organization::class, $orga );
	}

	/**
	 * Tests DAO::getOneToMany()
	 */
	public function testGetOneToMany() {
		$orga = DAO::getOne ( Organization::class, 'domain="lecnam.net"', false );
		$this->assertEquals ( "Conservatoire National des Arts et Métiers", $orga->getName () );
		$this->assertEquals ( 1, $orga->getId () );
		$users = DAO::getOneToMany ( $orga, 'users' );
		$this->assertTrue ( is_array ( $users ) );

		$this->assertTrue ( sizeof ( $users ) > 0 );
		$user = current ( $users );
		$this->assertInstanceOf ( User::class, $user );
	}

	/**
	 * Tests DAO::getManyToMany()
	 */
	public function testGetManyToMany() {
		$user = $this->dao->getOne ( User::class, "email='benjamin.sherman@gmail.com'", false );
		$groupes = DAO::getManyToMany ( $user, 'groupes' );
		$this->assertTrue ( is_array ( $groupes ) );
		$this->assertTrue ( sizeof ( $groupes ) > 0 );
		$groupe = current ( $groupes );
		$this->assertInstanceOf ( Groupe::class, $groupe );
	}

	/**
	 * Tests DAO::affectsManyToManys()
	 */
	public function testAffectsManyToManys() {
		// TODO Auto-generated DAOTest::testAffectsManyToManys()
		$this->markTestIncomplete ( "affectsManyToManys test not implemented" );

		DAO::affectsManyToManys(/* parameters */);
	}

	/**
	 * Tests DAO::getAll()
	 */
	public function testGetAll() {
		$users = $this->dao->getAll ( User::class );
		$this->assertEquals ( 101, sizeof ( $users ) );
		$user = current ( $users );
		$this->assertInstanceOf ( User::class, $user );
		$orga = $user->getOrganization ();
		$this->assertInstanceOf ( Organization::class, $orga );
	}

	/**
	 * Tests DAO::paginate()
	 */
	public function testPaginate() {
		$users = $this->dao->paginate ( User::class );
		$this->assertEquals ( 20, sizeof ( $users ) );
		$user = current ( $users );
		$this->assertInstanceOf ( User::class, $user );
		$users = $this->dao->paginate ( User::class, 2, 10 );
		$this->assertEquals ( 10, sizeof ( $users ) );
		$users = $this->dao->paginate ( User::class, 1, 10, 'email="benjamin.sherman@gmail.com"' );
		$this->assertEquals ( 1, sizeof ( $users ) );
		$user = current ( $users );
		$this->assertEquals ( 'Benjamin', $user->getFirstname () );
	}

	/**
	 * Tests DAO::getRownum()
	 */
	public function testGetRownum() {
		$users = $this->dao->getAll ( User::class, '', false );
		$users = array_values ( $users );
		$index = rand ( 0, sizeof ( $users ) - 1 );
		$this->assertEquals ( $index + 1, $this->dao->getRownum ( User::class, $users [$index]->getId () ) );
	}

	/**
	 * Tests DAO::count()
	 */
	public function testCount() {
		$this->assertEquals ( 101, $this->dao->count ( User::class ) );
	}

	/**
	 * Tests DAO::startDatabase()
	 */
	public function testStartDatabase() {
		DAO::startDatabase ( $this->config, 'default' );
		$this->assertTrue ( DAO::isConnected () );
		$this->assertInstanceOf ( Database::class, DAO::$db ['default'] );
		$this->assertInstanceOf ( PDO::class, DAO::$db ['default']->getDbObject () );
	}

	/**
	 * Tests DAO::startDatabaseMysqli()
	 */
	public function testStartDatabaseMysqli() {
		$this->config = include ROOT . 'config/config.php';
		DAO::startDatabase ( $this->config, 'mysqli' );
		$this->assertTrue ( DAO::isConnected () );
		$this->assertInstanceOf ( Database::class, DAO::$db ['mysqli'] );
		$this->assertInstanceOf ( mysqli::class, DAO::$db ['mysqli']->getDbObject () );
	}

	/**
	 * Tests DAO::getOne()
	 */
	public function testGetOne() {
		$user = $this->dao->getOne ( User::class, 'firstname="Benjamin"' );
		$this->assertInstanceOf ( User::class, $user );
	}

	/**
	 * Tests DAO::getById()
	 */
	public function testGetById() {
		$user = $this->dao->getById ( User::class, 1 );
		$this->assertInstanceOf ( User::class, $user );
	}

	/**
	 * Tests DAO::uCount()
	 */
	public function testUCount() {
		$res = DAO::uCount ( User::class, "firstname like ? or lastname like ?", [ "b%","a%" ] );
		$this->assertEquals ( 8, $res );
	}

	/**
	 * Tests DAO::uGetAll()
	 */
	public function testuGetAll() {
		$res = DAO::uGetAll ( User::class, "firstname like ? or lastname like ?", false, [ "b%","a%" ] );
		$this->assertEquals ( 8, sizeof ( $res ) );
		$this->assertEquals ( "benjamin.sherman@gmail.com", current ( $res ) . "" );
	}

	/**
	 * Tests DAO::UGetAllWithQuery()
	 */
	public function testUGetAllWithQuery() {
		$users = DAO::uGetAll ( User::class, "groupes.name = ?", [ "groupes" ], [ "Etudiants" ] );
		$this->assertEquals ( "jeremy.bryan", current ( $users ) . "" );
		$this->assertEquals ( 8, sizeof ( $users ) . "" );
	}

	/**
	 * Tests DAO::isConnected()
	 */
	public function testIsConnected() {
		$this->assertTrue ( $this->dao->isConnected () );
	}

	/**
	 * Tests DAO::insert()
	 */
	public function testInsert() {
		$count = DAO::count ( Organization::class );
		$orga = new Organization ();
		$orga->setName ( "orga test" );
		$orga->setDomain ( "dom.com" );
		$orga->setAliases ( "orga alias" );
		$this->dao->insert ( $orga );
		$this->assertEquals ( $count + 1, DAO::count ( Organization::class ) );
		DAO::remove ( $orga );
		$this->assertEquals ( $count, DAO::count ( Organization::class ) );
	}

	/**
	 * Tests DAO::beginTransaction
	 */
	public function testBeginTransaction() {
		$count = $this->dao->count ( Organization::class );
		$this->dao->beginTransaction ();
		$orga = new Organization ();
		$orga->setName ( "orga test" );
		$orga->setDomain ( "dom.com" );
		$orga->setAliases ( "orga alias" );
		$this->dao->insert ( $orga );
		$this->assertEquals ( $count + 1, DAO::count ( Organization::class ) );
		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $count, $this->dao->count ( Organization::class ) );
	}

	/**
	 * Tests DAO::Transaction
	 */
	public function testTransaction() {
		$count = $this->dao->count ( Organization::class );
		$this->dao->beginTransaction ();
		$orga = new Organization ();
		$orga->setName ( "orga test" );
		$orga->setDomain ( "dom.com" );
		$orga->setAliases ( "orga alias" );
		$this->dao->insert ( $orga );
		$this->assertEquals ( $count + 1, DAO::count ( Organization::class ) );
		$this->dao->commit ();
		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $count + 1, $this->dao->count ( Organization::class ) );

		$this->dao->beginTransaction ();
		DAO::remove ( $orga );
		$this->dao->commit ();
		$this->assertEquals ( $count, DAO::count ( Organization::class ) );
	}

	/**
	 * Tests DAO::rollback
	 */
	public function testRollback() {
		$count = $this->dao->count ( Organization::class );
		$this->dao->beginTransaction ();
		$orga = new Organization ();
		$orga->setName ( "orga test" );
		$orga->setDomain ( "dom.com" );
		$orga->setAliases ( "orga alias" );
		$this->dao->insert ( $orga );
		$this->assertEquals ( $count + 1, DAO::count ( Organization::class ) );
		$this->dao->rollBack ();
		$this->assertEquals ( $count, DAO::count ( Organization::class ) );
		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $count, $this->dao->count ( Organization::class ) );
	}

	/**
	 * Tests DAO::multipleTransactions
	 */
	public function testMultipleTransactions() {
		$count = $this->dao->count ( Organization::class );
		$this->dao->beginTransaction ();

		$orga = new Organization ();
		$orga->setName ( "orga test" );
		$orga->setDomain ( "dom.com" );
		$orga->setAliases ( "orga alias" );
		$this->dao->insert ( $orga );

		$orga2 = new Organization ();
		$orga2->setName ( "orga2 test" );
		$orga2->setDomain ( "dom2.com" );
		$orga2->setAliases ( "orga2 alias" );
		$this->dao->insert ( $orga2 );
		$this->assertEquals ( $count + 2, DAO::count ( Organization::class ) );
		$this->dao->commit ();
		$this->assertEquals ( $count + 2, DAO::count ( Organization::class ) );

		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $count + 2, $this->dao->count ( Organization::class ) );

		$this->dao->beginTransaction ();
		DAO::remove ( $orga );
		DAO::remove ( $orga2 );
		$this->dao->commit ();
		$this->assertEquals ( $count, DAO::count ( Organization::class ) );
	}

	private function addUser($orga) {
		$user = new User ();
		$user->setFirstname ( 'DOE' );
		$user->setLastname ( 'John' );
		$user->setEmail ( 'john.doe@local.fr' );
		$user->setPassword ( '0000' );
		$user->setOrganization ( $orga );
		$this->dao->insert ( $user );
		return $user;
	}

	private function addOrga() {
		$orga = new Organization ();
		$orga->setName ( "orga test" );
		$orga->setDomain ( "dom.com" );
		$orga->setAliases ( "orga alias" );
		$this->dao->insert ( $orga );
		return $orga;
	}

	/**
	 * Tests DAO::nested
	 */
	public function testNestedTransactions() {
		$countOrgas = $this->dao->count ( Organization::class );
		$countUsers = $this->dao->count ( User::class );

		$this->dao->beginTransaction ();

		$orga = $this->addOrga ();
		$this->dao->beginTransaction ();
		$oOrga = $this->dao->getById ( Organization::class, 1 );
		$user = $this->addUser ( $oOrga );
		$this->dao->commit ();
		$this->dao->commit ();

		$this->assertEquals ( $countOrgas + 1, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers + 1, DAO::count ( User::class ) );

		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $countOrgas + 1, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers + 1, DAO::count ( User::class ) );

		$this->dao->beginTransaction ();
		DAO::remove ( $orga );
		DAO::remove ( $user );
		$this->dao->commit ();
		$this->assertEquals ( $countOrgas, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers, DAO::count ( User::class ) );
	}

	/**
	 * Tests DAO::nested rollback
	 */
	public function testNestedRollbackTransactions() {
		$countOrgas = $this->dao->count ( Organization::class );
		$countUsers = $this->dao->count ( User::class );

		$this->dao->beginTransaction ();

		$this->addOrga ();
		$this->dao->beginTransaction ();
		$oOrga = $this->dao->getOne ( Organization::class, 1 );
		$this->addUser ( $oOrga );
		$this->dao->rollBack ();
		$this->dao->rollBack ();

		$this->assertEquals ( $countOrgas, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers, DAO::count ( User::class ) );

		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $countOrgas, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers, DAO::count ( User::class ) );
	}

	/**
	 * Tests DAO::nested commitAll
	 */
	public function testNestedCommitAll() {
		$countOrgas = $this->dao->count ( Organization::class );
		$countUsers = $this->dao->count ( User::class );

		$this->dao->beginTransaction ();

		$orga = $this->addOrga ();
		$this->dao->beginTransaction ();
		$oOrga = $this->dao->getOne ( Organization::class, 1 );
		$user = $this->addUser ( $oOrga );
		$this->dao->commitAll ();

		$this->assertEquals ( $countOrgas + 1, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers + 1, DAO::count ( User::class ) );

		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $countOrgas + 1, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers + 1, DAO::count ( User::class ) );

		$this->dao->beginTransaction ();
		DAO::remove ( $orga );
		DAO::remove ( $user );
		$this->dao->commit ();
		$this->assertEquals ( $countOrgas, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers, DAO::count ( User::class ) );
	}

	/**
	 * Tests DAO::nested rollbackAll
	 */
	public function testNestedRollbackAllTransactions() {
		$countOrgas = $this->dao->count ( Organization::class );
		$countUsers = $this->dao->count ( User::class );

		$this->dao->beginTransaction ();

		$this->addOrga ();
		$this->dao->beginTransaction ();
		$oOrga = $this->dao->getOne ( Organization::class, 1 );
		$this->addUser ( $oOrga );
		$this->dao->rollBackAll ();

		$this->assertEquals ( $countOrgas, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers, DAO::count ( User::class ) );

		$this->dao->closeDb ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
		$this->assertEquals ( $countOrgas, DAO::count ( Organization::class ) );
		$this->assertEquals ( $countUsers, DAO::count ( User::class ) );
	}

	/**
	 * Tests DAO::GetDatabase
	 */
	public function testGetDatabase() {
		$this->config = include ROOT . 'config/config.php';
		$this->assertEquals ( $db1 = $this->dao->getDatabase (), $this->dao->getDatabase ( 'default' ) );
		$this->assertTrue ( $db1->isConnected () );
		$db1->close ();
		$this->assertFalse ( $db1->isConnected () );
		$db2 = $this->dao->getDatabase ( 'mysqli' );
		$this->assertTrue ( $db2->isConnected () );
		$db2->close ();
		$this->assertFalse ( $db2->isConnected () );
	}

	/**
	 * Tests DAO::GetDbOffset
	 */
	public function testGetDbOffset() {
		$this->config = include ROOT . 'config/config.php';
		$dbConfig1 = $this->dao->getDbOffset ( $this->config );
		$this->assertEquals ( $dbConfig1 ['dbName'], 'messagerie' );
		$dbConfig2 = $this->dao->getDbOffset ( $this->config, 'mysqli' );
		$this->assertEquals ( $dbConfig2 ['dbName'], 'messagerie' );
		$this->assertEquals ( $dbConfig2 ['wrapper'], "\\Ubiquity\\db\\providers\\mysqli\\MysqliWrapper" );
	}

	/**
	 * Tests DAO::GetDatabases
	 */
	public function testGetDatabases() {
		$this->config = include ROOT . 'config/config.php';
		Startup::$config = $this->config;
		$dbs = $this->dao->getDatabases ();
		$this->assertEquals ( 2, sizeof ( $dbs ) );
	}
}

