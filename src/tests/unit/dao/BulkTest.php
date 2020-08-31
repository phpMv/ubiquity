<?php
use Ubiquity\orm\DAO;
use models\bench\World;

/**
 * DAOBulk test case.
 */
class BulkTest extends BaseTest {

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
		$this->dao->setModelDatabase ( World::class, 'bench' );
		$this->_startDatabase ( $this->dao, 'bench' );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao->closeDb ();
	}

	public function testInsertDelete() {
		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10000, \count ( $worlds ) );
		for($i = 0; $i < 10; $i ++) {
			$world = new World ();
			$world->randomNumber = \mt_rand ( 1, 10000 );
			$this->dao->toInsert ( $world );
		}
		$this->dao->flushInserts ();
		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10010, \count ( $worlds ) );
		$newWorlds = [ ];
		for($i = 0; $i < 10; $i ++) {
			$world = new World ();
			$world->randomNumber = \mt_rand ( 1, 10000 );
			$newWorlds [] = $world;
		}
		$this->dao->toInserts ( $newWorlds );
		$this->dao->flushInserts ();

		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10020, \count ( $worlds ) );

		$worlds = $this->dao->getAll ( World::class, '1=1 ORDER BY id DESC Limit 20' );
		$this->assertEquals ( 20, \count ( $worlds ) );
		$this->dao->toDeletes ( $worlds );
		$this->dao->flushDeletes ();

		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10000, \count ( $worlds ) );
	}

	public function testUpdates() {
		$worlds = $this->dao->getAll ( World::class, '1=1 Limit 10' );
		$this->assertEquals ( 10, \count ( $worlds ) );
		foreach ( $worlds as $world ) {
			do {
				$nRn = \mt_rand ( 1, 10000 );
			} while ( $nRn === $world->randomNumber );
			$world->randomNumber = $nRn;
			$this->dao->toUpdate ( $world );
		}
		$this->dao->flushUpdates ();
		$updatedWorlds = $this->dao->getAll ( World::class, '1=1 Limit 10' );
		$this->assertEquals ( 10, $this->countUpdated ( $worlds, $updatedWorlds ) );

		$worlds = $this->dao->getAll ( World::class, '1=1 ORDER BY id DESC Limit 10' );
		$this->assertEquals ( 10, \count ( $worlds ) );
		foreach ( $worlds as $world ) {
			do {
				$nRn = \mt_rand ( 1, 10000 );
			} while ( $nRn === $world->randomNumber );
			$world->randomNumber = $nRn;
		}
		$this->dao->toUpdates ( $worlds );
		$this->dao->flushUpdates ();
		$updatedWorlds = $this->dao->getAll ( World::class, '1=1 ORDER BY id DESC Limit 10' );
		$this->assertEquals ( 10, $this->countUpdated ( $worlds, $updatedWorlds ) );
	}

	protected function countUpdated($worlds, $updatedWorlds) {
		$count = 0;
		$worlds = array_values ( $worlds );
		$updatedWorlds = array_values ( $updatedWorlds );
		foreach ( $worlds as $index => $world ) {
			if ($world->randomNumber !== $updatedWorlds [$index]->randomNumber) {
				$count ++;
			}
		}
		return $count;
	}

	protected function getDatabase() {
		return 'bench';
	}
}

