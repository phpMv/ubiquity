<?php
use Ubiquity\cache\CacheManager;
use Ubiquity\orm\DAO;
use models\Organization;
use models\User;
use Ubiquity\cache\system\ArrayCache;

/**
 * Startup test case.
 */
class DAOWithApcTest extends BaseTest {

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
		$this->_startServices ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
	}

	protected function getCacheSystem() {
		return ArrayCache::class;
	}

	protected function _startServices($what = false) {
		CacheManager::initCache ( $this->oConfig );
		$this->_startCache ();
		$this->_startRouter ( $what );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao->closeDb ();
	}

	/**
	 * Tests DAO::getManyToOne()
	 */
	public function testGetManyToOne() {
		$user = $this->dao->getOne ( User::class, "email='benjamin.sherman@gmail.com'", false );
		$orga = DAO::getManyToOne ( $user, 'organization' );
		$this->assertInstanceOf ( Organization::class, $orga );
	}

	protected function getDi() {
		return array ("*.allS" => function ($controller) {
			return new \services\IAllService ();
		},"*.inj" => function ($ctrl) {
			return new \services\IAllService ();
		},"@exec" => array ("jquery" => function ($controller) {
			return \Ajax\php\ubiquity\JsUtils::diSemantic ( $controller );
		} ) );
	}
}

