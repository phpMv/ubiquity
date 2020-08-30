<?php
use Ubiquity\orm\SDAO;
use models\bench\Fortune;

/**
 * DAO test case.
 */
class DAOTest extends BaseTest {

	/**
	 *
	 * @var SDAO
	 */
	private $dao;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->dao = new SDAO ();
		$this->_startCache ();
		$this->_startDatabase ( $this->dao, 'bench' );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao->closeDb ();
	}

	public function testGetPrepared() {
		$fortune = SDAO::getById ( Fortune::class, [ 1 ] );
		$this->assertEquals ( 1, $fortune->id );
		$this->assertEquals ( 'fortune: No such file or directory', $fortune->message );
	}
}

