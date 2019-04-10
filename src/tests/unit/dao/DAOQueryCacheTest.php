<?php
use Ubiquity\orm\DAO;
use models\User;
use models\Organization;
use models\Groupe;
use Ubiquity\cache\database\QueryCache;

/**
 * DAO test case.
 */
class DAOQueryCacheTest extends BaseTest {

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
		$this->_loadConfig ();
		$this->config ["database"] ["cache"] = QueryCache::class;
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
		$user = $this->dao->getOne ( User::class, "email='benjamin.sherman@gmail.com'", false, null, true );
		$orga = DAO::getManyToOne ( $user, 'organization', false, true );
		$this->assertInstanceOf ( Organization::class, $orga );
	}

	/**
	 * Tests DAO::getOneToMany()
	 */
	public function testGetOneToMany() {
		$orga = DAO::getOne ( Organization::class, 'domain="lecnam.net"', false, null, false );
		$this->assertEquals ( "Conservatoire National des Arts et Métiers", $orga->getName () );
		$this->assertEquals ( 1, $orga->getId () );
		$users = DAO::getOneToMany ( $orga, 'users', true, true );
		$this->assertTrue ( is_array ( $users ) );

		$this->assertTrue ( sizeof ( $users ) > 0 );
		$user = current ( $users );
		$this->assertInstanceOf ( User::class, $user );
	}

	/**
	 * Tests DAO::getManyToMany()
	 */
	public function testGetManyToMany() {
		$user = $this->dao->getOne ( User::class, "email='benjamin.sherman@gmail.com'", false, null, true );
		$groupes = DAO::getManyToMany ( $user, 'groupes', false, null, true );
		$this->assertTrue ( is_array ( $groupes ) );
		$this->assertTrue ( sizeof ( $groupes ) > 0 );
		$groupe = current ( $groupes );
		$this->assertInstanceOf ( Groupe::class, $groupe );
	}

	/**
	 * Tests DAO::getAll()
	 */
	public function testGetAll() {
		$users = $this->dao->getAll ( User::class, '', true, null, true );
		$this->assertEquals ( 101, sizeof ( $users ) );
		$user = current ( $users );
		$this->assertInstanceOf ( User::class, $user );
		$orga = $user->getOrganization ();
		$this->assertInstanceOf ( Organization::class, $orga );
	}

	/**
	 * Tests DAO::getRownum()
	 */
	public function testGetRownum() {
		$users = $this->dao->getAll ( User::class, '', false, null, true );
		$users = array_values ( $users );
		$index = rand ( 0, sizeof ( $users ) - 1 );
		$this->assertEquals ( $index + 1, $this->dao->getRownum ( User::class, $users [$index]->getId () ) );
	}

	/**
	 * Tests DAO::getOne()
	 */
	public function testGetOne() {
		$user = $this->dao->getOne ( User::class, 'firstname="Benjamin"', true, null, true );
		$this->assertInstanceOf ( User::class, $user );
	}

	/**
	 * Tests DAO::uGetAll()
	 */
	public function testuGetAll() {
		$res = DAO::uGetAll ( User::class, "firstname like ? or lastname like ?", false, [ "b%","a%" ], true );
		$this->assertEquals ( 8, sizeof ( $res ) );
		$this->assertEquals ( "benjamin.sherman@gmail.com", current ( $res ) . "" );
	}

	/**
	 * Tests DAO::UGetAllWithQuery()
	 */
	public function testUGetAllWithQuery() {
		$users = DAO::uGetAll ( User::class, "groupes.name = ?", [ "groupes" ], [ "Etudiants" ], true );
		$this->assertEquals ( "jeremy.bryan", current ( $users ) . "" );
		$this->assertEquals ( 8, sizeof ( $users ) . "" );
	}
}

