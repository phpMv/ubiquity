<?php

use Ubiquity\orm\DAO;
use models\User;
use Ubiquity\utils\models\UModel;
use models\Groupe;

/**
 * UModel test case.
 */
class UModelTest extends BaseTest {

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
		$this->dao->start();
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->dao->closeDb ();
	}

	/**
	 * Tests UModel::toggleProperty()
	 */
	public function testToggleProperty() {
		$u=$this->dao->getById(User::class, 1);
		$s=$u->getSuspended();
		UModel::toggleProperty($u, 'suspended');
		$this->assertFalse($s==$u->getSuspended());
		UModel::toggleProperty($u, 'suspended');
		$this->assertTrue($s==$u->getSuspended());
	}

	/**
	 * Tests UModel::incProperty()
	 */
	public function testIncProperty() {
		$u=$this->dao->getById(User::class, 1);
		$id=$u->getId();
		$this->assertEquals(1,$id);
		UModel::incProperty($u, 'id');
		$this->assertEquals(2,$u->getId());
	}

	/**
	 * Tests UModel::decProperty()
	 */
	public function testDecProperty() {
		$u=$this->dao->getById(User::class, 2);
		$id=$u->getId();
		$this->assertEquals(2,$id);
		UModel::decProperty($u, 'id');
		$this->assertEquals(1,$u->getId());
	}

	/**
	 * Tests UModel::concatProperty()
	 */
	public function testConcatProperty() {
		$u=$this->dao->getById(User::class, 1);
		$fn=$u->getFirstname();
		UModel::concatProperty($u, 'firstname','after');
		$this->assertEquals($fn.'after',$u->getFirstname());
		UModel::concatProperty($u, 'firstname','before',false);
		$this->assertEquals('before'.$fn.'after',$u->getFirstname());
	}

	/**
	 * Tests UModel::addTo()
	 */
	public function testAddToRemoveFrom() {
		$u=$this->dao->getById(User::class, 1,['groupes']);
		$count=\count($u->getGroupes());
		$gr=new Groupe();
		UModel::addTo($u, 'groupes',$gr);
		$this->assertEquals($count+1,\count($u->getGroupes()));
		UModel::removeFrom($u, 'groupes', $gr);
		$this->assertEquals($count,\count($u->getGroupes()));
	}


	/**
	 * Tests UModel::removeFromByIndex()
	 */
	public function testRemoveFromByIndex() {
		$u=$this->dao->getById(User::class, 7,true);
		$groupes=$u->getGroupes();
		$this->assertEquals(0,\count($groupes));
		UModel::removeFromByIndex($u, 'groupes',20);
		$this->assertEquals(0,\count($u->getGroupes()));
	}

	/**
	 * Tests UModel::asArray()
	 */
	public function testAsArray() {
		// TODO Auto-generated UModelTest::testAsArray()
		$this->markTestIncomplete ( "asArray test not implemented" );

		UModel::asArray(/* parameters */);
	}

	/**
	 * Tests UModel::asJson()
	 */
	public function testAsJson() {
		// TODO Auto-generated UModelTest::testAsJson()
		$this->markTestIncomplete ( "asJson test not implemented" );

		UModel::asJson(/* parameters */);
	}

	/**
	 * Tests UModel::asArrayProperties()
	 */
	public function testAsArrayProperties() {
		// TODO Auto-generated UModelTest::testAsArrayProperties()
		$this->markTestIncomplete ( "asArrayProperties test not implemented" );

		UModel::asArrayProperties(/* parameters */);
	}

	/**
	 * Tests UModel::asJsonProperties()
	 */
	public function testAsJsonProperties() {
		// TODO Auto-generated UModelTest::testAsJsonProperties()
		$this->markTestIncomplete ( "asJsonProperties test not implemented" );

		UModel::asJsonProperties(/* parameters */);
	}

	/**
	 * Tests UModel::equals()
	 */
	public function testEquals() {
		$u1=$this->dao->getById(User::class, 1);
		$u2=$this->dao->getById(User::class, 2);
		$this->assertTrue(UModel::equals($u1, $u1));
		$this->assertFalse(UModel::equals($u1, $u2));
	}
}

