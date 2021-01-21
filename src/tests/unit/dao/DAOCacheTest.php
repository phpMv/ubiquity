<?php
use Ubiquity\orm\DAO;
use models\User;
use models\Organization;
use Ubiquity\db\Database;
use models\Groupe;
use Ubiquity\controllers\Startup;

/**
 * DAOCache test case.
 */
class DAOCacheTest extends BaseTest {

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
		$this->dao->setCache ( new \Ubiquity\cache\dao\DAOMemoryCache () );
		$this->_startDatabase ( $this->dao );
		$this->dao->warmupCache ( Organization::class );
		$this->dao->warmupCache ( User::class, 'id< ?', false, [ 5 ] );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao->closeDb ();
	}

	public function testDirectFetch() {
		$cache = $this->dao->getCache ();
		$orgas = $this->dao->getAll ( Organization::class );
		foreach ( $orgas as $orga ) {
			$cOrga = $cache->fetch ( Organization::class, $orga->getId () );
			$this->assertEquals ( $orga->getName (), $cOrga->getName () );
		}

		$users = $this->dao->getAll ( User::class, 'id< ?', false, [ 5 ] );
		foreach ( $users as $user ) {
			$cUser = $cache->fetch ( User::class, $user->getId () );
			$this->assertEquals ( $user->getFirstname (), $cUser->getFirstname () );
			$this->assertEquals ( $user->getLastname (), $cUser->getLastname () );
		}
	}

	public function testDeleteCache() {
		$cache = $this->dao->getCache ();
		$this->assertInstanceOf ( Organization::class, $cache->fetch ( Organization::class, 1 ) );
		$cache->delete ( Organization::class, 1 );
		$this->assertFalse ( $cache->fetch ( Organization::class, 1 ) );
	}
}

