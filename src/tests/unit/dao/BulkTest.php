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
		$this->assertEquals ( 0, $this->dao->countInstancesBulk ( World::class, 'insert' ) );
		$this->assertEquals ( 10000, \count ( $worlds ) );
		for($i = 0; $i < 10; $i ++) {
			$world = new World ();
			$world->randomNumber = \mt_rand ( 1, 10000 );
			$this->dao->toInsert ( $world );
		}
		$this->assertEquals ( 10, $this->dao->countInstancesBulk ( World::class, 'insert' ) );
		$this->dao->flushInserts ();
		$this->assertEquals ( 0, $this->dao->countInstancesBulk ( World::class, 'insert' ) );
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

	public function testFlush() {
		$id1 = \mt_rand ( 1, 10000 );
		$world1 = $this->dao->getById ( World::class, [ $id1 ] );
		$world1->randomNumber = 10001;
		$this->dao->toUpdate ( $world1 );

		$id2 = \mt_rand ( 1, 10000 );
		$world2 = $this->dao->getById ( World::class, [ $id2 ] );
		$this->dao->toDelete ( $world2 );

		$world3 = new World ();
		$world3->randomNumber = mt_rand ( 1, 10000 );
		$this->dao->toInsert ( $world3 );

		$this->dao->flush ();

		$world1 = $this->dao->getById ( World::class, [ $id1 ] );
		$this->assertEquals ( 10001, $world1->randomNumber );

		$world2 = $this->dao->getById ( World::class, [ $id2 ] );

		$this->assertNull ( $world2 );

		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10000, \count ( $worlds ) );
	}

	public function testClear() {
		$this->assertEquals ( 0, $this->dao->countInstancesBulk ( World::class, 'update' ) );
		$this->assertEquals ( 0, $this->dao->countInstancesBulk ( World::class, 'delete' ) );
		$id1 = \mt_rand ( 1, 10000 );
		$world1 = $this->dao->getById ( World::class, [ $id1 ] );
		$world1->randomNumber = 10001;
		$this->dao->toUpdate ( $world1 );
		$this->assertEquals ( 1, $this->dao->countInstancesBulk ( World::class, 'update' ) );

		$id2 = \mt_rand ( 1, 10000 );
		$world2 = $this->dao->getById ( World::class, [ $id2 ] );
		$this->dao->toDelete ( $world2 );
		$this->assertEquals ( 1, $this->dao->countInstancesBulk ( World::class, 'delete' ) );

		$world3 = new World ();
		$world3->randomNumber = mt_rand ( 1, 10000 );
		$this->dao->toInsert ( $world3 );

		$this->dao->clearBulks ();
		$this->assertEquals ( 0, $this->dao->countInstancesBulk ( World::class, 'update' ) );
		$this->assertEquals ( 0, $this->dao->countInstancesBulk ( World::class, 'delete' ) );

		$world3 = new World ();
		$world3->randomNumber = mt_rand ( 1, 10000 );
		$this->dao->toInsert ( $world3 );
		$this->assertEquals ( 1, $this->dao->countInstancesBulk ( World::class, 'insert' ) );
		$this->dao->clearBulks ( [ 'insert' ], [ World::class ] );
		$this->assertEquals ( 0, $this->dao->countInstancesBulk ( World::class, 'insert' ) );
	}

	public function testUpdateGroup() {
		$worlds = $this->dao->getAll ( World::class, '1=1 Limit 10' );
		$this->assertEquals ( 10, \count ( $worlds ) );
		foreach ( $worlds as $world ) {
			do {
				$nRn = \mt_rand ( 1, 10000 );
			} while ( $nRn === $world->randomNumber );
			$world->randomNumber = $nRn;
			$this->dao->toUpdate ( $world );
		}
		$this->dao->updateGroups ();
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
		$this->dao->updateGroups ( 2 );
		$updatedWorlds = $this->dao->getAll ( World::class, '1=1 ORDER BY id DESC Limit 10' );
		$this->assertEquals ( 10, $this->countUpdated ( $worlds, $updatedWorlds ) );
	}

	public function testInsertDeleteGroups() {
		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10000, \count ( $worlds ) );
		for($i = 0; $i < 10; $i ++) {
			$world = new World ();
			$world->randomNumber = \mt_rand ( 1, 10000 );
			$this->dao->toInsert ( $world );
		}
		$this->dao->insertGroups ();
		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10010, \count ( $worlds ) );
		$newWorlds = [ ];
		for($i = 0; $i < 10; $i ++) {
			$world = new World ();
			$world->randomNumber = \mt_rand ( 1, 10000 );
			$newWorlds [] = $world;
		}
		$this->dao->toInserts ( $newWorlds );
		$this->dao->insertGroups ( 2 );

		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10020, \count ( $worlds ) );

		$worlds = $this->dao->getAll ( World::class, '1=1 ORDER BY id DESC Limit 20' );
		$this->assertEquals ( 20, \count ( $worlds ) );
		$this->dao->toDeletes ( $worlds );
		$this->dao->deleteGroups ();

		$worlds = $this->dao->getAll ( World::class );
		$this->assertEquals ( 10000, \count ( $worlds ) );
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

