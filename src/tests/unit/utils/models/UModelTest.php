<?php

use Ubiquity\orm\DAO;
use models\User;
use Ubiquity\utils\models\UModel;

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
		// TODO Auto-generated UModelTest::testConcatProperty()
		$this->markTestIncomplete ( "concatProperty test not implemented" );

		UModel::concatProperty(/* parameters */);
	}

	/**
	 * Tests UModel::addTo()
	 */
	public function testAddTo() {
		// TODO Auto-generated UModelTest::testAddTo()
		$this->markTestIncomplete ( "addTo test not implemented" );

		UModel::addTo(/* parameters */);
	}

	/**
	 * Tests UModel::removeFrom()
	 */
	public function testRemoveFrom() {
		// TODO Auto-generated UModelTest::testRemoveFrom()
		$this->markTestIncomplete ( "removeFrom test not implemented" );

		UModel::removeFrom(/* parameters */);
	}

	/**
	 * Tests UModel::removeFromByIndex()
	 */
	public function testRemoveFromByIndex() {
		// TODO Auto-generated UModelTest::testRemoveFromByIndex()
		$this->markTestIncomplete ( "removeFromByIndex test not implemented" );

		UModel::removeFromByIndex(/* parameters */);
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
		// TODO Auto-generated UModelTest::testEquals()
		$this->markTestIncomplete ( "equals test not implemented" );

		UModel::equals(/* parameters */);
	}
}

