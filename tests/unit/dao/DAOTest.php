<?php
use Ubiquity\orm\DAO;
use models\User;

require_once 'Ubiquity/orm/DAO.php';
require_once 'tests/unit/config/app/models/Groupe.php';
require_once 'tests/unit/config/app/models/Organization.php';
require_once 'tests/unit/config/app/models/Organizationsettings.php';
require_once 'tests/unit/config/app/models/Settings.php';
require_once 'tests/unit/config/app/models/User.php';
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
		$this->dao = new DAO ();
		$this->_loadConfig ();
		$this->_startCache ();
		$this->_startDatabase ( $this->dao );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao = null;
	}

	/**
	 * Tests DAO::getManyToOne()
	 */
	public function testGetManyToOne() {
		// TODO Auto-generated DAOTest::testGetManyToOne()
		$this->markTestIncomplete ( "getManyToOne test not implemented" );

		DAO::getManyToOne(/* parameters */);
	}

	/**
	 * Tests DAO::getOneToMany()
	 */
	public function testGetOneToMany() {
		// TODO Auto-generated DAOTest::testGetOneToMany()
		$this->markTestIncomplete ( "getOneToMany test not implemented" );

		DAO::getOneToMany(/* parameters */);
	}

	/**
	 * Tests DAO::getManyToMany()
	 */
	public function testGetManyToMany() {
		// TODO Auto-generated DAOTest::testGetManyToMany()
		$this->markTestIncomplete ( "getManyToMany test not implemented" );

		DAO::getManyToMany(/* parameters */);
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
		$this->dao->getAll ( User::class );
	}

	/**
	 * Tests DAO::paginate()
	 */
	public function testPaginate() {
		// TODO Auto-generated DAOTest::testPaginate()
		$this->markTestIncomplete ( "paginate test not implemented" );

		DAO::paginate(/* parameters */);
	}

	/**
	 * Tests DAO::getRownum()
	 */
	public function testGetRownum() {
		// TODO Auto-generated DAOTest::testGetRownum()
		$this->markTestIncomplete ( "getRownum test not implemented" );

		DAO::getRownum(/* parameters */);
	}

	/**
	 * Tests DAO::count()
	 */
	public function testCount() {
		// TODO Auto-generated DAOTest::testCount()
		$this->markTestIncomplete ( "count test not implemented" );

		DAO::count(/* parameters */);
	}

	/**
	 * Tests DAO::getOne()
	 */
	public function testGetOne() {
		// TODO Auto-generated DAOTest::testGetOne()
		$this->markTestIncomplete ( "getOne test not implemented" );

		DAO::getOne(/* parameters */);
	}

	/**
	 * Tests DAO::connect()
	 */
	public function testConnect() {
		// TODO Auto-generated DAOTest::testConnect()
		$this->markTestIncomplete ( "connect test not implemented" );

		DAO::connect(/* parameters */);
	}

	/**
	 * Tests DAO::isConnected()
	 */
	public function testIsConnected() {
		// TODO Auto-generated DAOTest::testIsConnected()
		$this->markTestIncomplete ( "isConnected test not implemented" );

		DAO::isConnected(/* parameters */);
	}
}

