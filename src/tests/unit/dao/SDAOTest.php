<?php
use Ubiquity\orm\SDAO;
use models\bench\Fortune;

/**
 * SDAO test case.
 */
class SDAOTest extends BaseTest {

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
		$this->dao->setModelDatabase ( Fortune::class, 'bench' );
		$this->_startDatabase ( $this->dao, 'bench' );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao->closeDb ();
	}
	
	public function testGetById() {
		$fortune = $this->dao->getById ( Fortune::class, [ 1 ] );
		$this->assertEquals ( 1, $fortune->id );
		$this->assertEquals ( 'Fortune: No such file or directory', $fortune->message );
	}

	public function testGetOne() {
		$fortune = $this->dao->getOne ( Fortune::class, 1 );
		$this->assertEquals ( 1, $fortune->id );
		$this->assertEquals ( 'Fortune: No such file or directory', $fortune->message );
	}

	public function testGetAll() {
		$fortunes = $this->dao->getAll ( Fortune::class );
		$this->assertEquals ( 12, \count ( $fortunes ) );
		$this->assertInstanceOf ( Fortune::class, \current ( $fortunes ) );

		$fortunes = $this->dao->getAll ( Fortune::class, 'id < ?', false, [ 6 ] );
		$this->assertEquals ( 5, \count ( $fortunes ) );
		$this->assertInstanceOf ( Fortune::class, \current ( $fortunes ) );
	}

	protected function getDatabase() {
		return 'bench';
	}
	
	protected function getCacheDirectory() {
		return "cache/";
	}

	public function testUpdate() {
		$fortune = $this->dao->getById ( Fortune::class, [ 1 ] );
		$this->assertEquals ( 1, $fortune->id );
		$this->assertEquals ( 'Fortune: No such file or directory', $fortune->message );
		$newMessage = 'New message for fortune';
		$fortune->message = $newMessage;
		$this->dao->update ( $fortune );
		$fortune = $this->dao->getById ( Fortune::class, [ 1 ] );
		$this->assertEquals ( $newMessage, $fortune->message );
	}
}

