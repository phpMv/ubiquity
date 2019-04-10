<?php
use models\User;
use Ubiquity\contents\transformation\TransformersManager;
use Ubiquity\orm\DAO;

/**
 * TransformersManager test case.
 */
class TransformersManagerTest extends BaseTest {
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
		$this->_startCache ();
		$this->_startDatabase ( $this->dao );
		TransformersManager::startProd ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->database = null;
	}

	protected function _display($callback) {
		ob_start ();
		$callback ();
		return ob_get_clean ();
	}

	/**
	 * Tests Perso transformer
	 */
	public function testPerso() {
		$user = $this->dao->getOne ( User::class, 1 );
		$password = $user->getPassword ();
		DAO::$transformerOp = 'toView';
		$user = $this->DAO->getOne ( User::class, 1 );
		$this->assertEquals ( sha1 ( $password ), $user->getPassword () );
	}
}

