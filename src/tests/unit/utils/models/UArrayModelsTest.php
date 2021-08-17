<?php

use Ubiquity\utils\models\UArrayModels;
use Ubiquity\orm\DAO;
use models\Organization;

/**
 * UArrayModels test case.
 */
class UArrayModelsTest extends BaseTest {

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
	 * Tests UArrayModels::sort()
	 */
	public function testSort() {
		// TODO Auto-generated UArrayModelsTest::testSort()
		$this->markTestIncomplete ( "sort test not implemented" );

		UArrayModels::sort(/* parameters */);
	}

	/**
	 * Tests UArrayModels::groupsBy()
	 */
	public function testGroupsBy() {
		// TODO Auto-generated UArrayModelsTest::testGroupsBy()
		$this->markTestIncomplete ( "groupsBy test not implemented" );

		UArrayModels::groupsBy(/* parameters */);
	}

	/**
	 * Tests UArrayModels::groupBy()
	 */
	public function testGroupBy() {
		// TODO Auto-generated UArrayModelsTest::testGroupBy()
		$this->markTestIncomplete ( "groupBy test not implemented" );

		UArrayModels::groupBy(/* parameters */);
	}

	/**
	 * Tests UArrayModels::asKeyValues()
	 */
	public function testAsKeyValues() {
		// TODO Auto-generated UArrayModelsTest::testAsKeyValues()
		$this->markTestIncomplete ( "asKeyValues test not implemented" );

		UArrayModels::asKeyValues(/* parameters */);
	}

	/**
	 * Tests UArrayModels::find()
	 */
	public function testFind() {
		$orgas=$this->dao->getAll(Organization::class);
		$orga=UArrayModels::find($orgas,fn($item)=>$item->getId()==1);
		$this->assertEquals(1,$orga->getId());
	}

	/**
	 * Tests UArrayModels::findBy()
	 */
	public function testFindBy() {
		$orgas=$this->dao->getAll(Organization::class);
		$orga=UArrayModels::findBy($orgas,1);
		$this->assertEquals(1,$orga->getId());
	}

	/**
	 * Tests UArrayModels::findById()
	 */
	public function testFindById() {
		$orgas=$this->dao->getAll(Organization::class);
		$orga=UArrayModels::findById($orgas,1);
		$this->assertEquals(1,$orga->getId());
	}

	/**
	 * Tests UArrayModels::contains()
	 */
	public function testContains() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertTrue(UArrayModels::contains($orgas,fn($item)=>$item->getId()==1));
	}

	/**
	 * Tests UArrayModels::containsBy()
	 */
	public function testContainsBy() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertTrue(UArrayModels::containsBy($orgas,1));
	}

	/**
	 * Tests UArrayModels::containsById()
	 */
	public function testContainsById() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertTrue(UArrayModels::containsById($orgas,1));
	}

	/**
	 * Tests UArrayModels::remove()
	 */
	public function testRemove() {
		// TODO Auto-generated UArrayModelsTest::testRemove()
		$this->markTestIncomplete ( "remove test not implemented" );

		UArrayModels::remove(/* parameters */);
	}

	/**
	 * Tests UArrayModels::removeBy()
	 */
	public function testRemoveBy() {
		// TODO Auto-generated UArrayModelsTest::testRemoveBy()
		$this->markTestIncomplete ( "removeBy test not implemented" );

		UArrayModels::removeBy(/* parameters */);
	}

	/**
	 * Tests UArrayModels::removeAllBy()
	 */
	public function testRemoveAllBy() {
		// TODO Auto-generated UArrayModelsTest::testRemoveAllBy()
		$this->markTestIncomplete ( "removeAllBy test not implemented" );

		UArrayModels::removeAllBy(/* parameters */);
	}

	/**
	 * Tests UArrayModels::compute()
	 */
	public function testCompute() {
		// TODO Auto-generated UArrayModelsTest::testCompute()
		$this->markTestIncomplete ( "compute test not implemented" );

		UArrayModels::compute(/* parameters */);
	}

	/**
	 * Tests UArrayModels::computeSumProperty()
	 */
	public function testComputeSumProperty() {
		// TODO Auto-generated UArrayModelsTest::testComputeSumProperty()
		$this->markTestIncomplete ( "computeSumProperty test not implemented" );

		UArrayModels::computeSumProperty(/* parameters */);
	}

	/**
	 * Tests UArrayModels::computeSum()
	 */
	public function testComputeSum() {
		// TODO Auto-generated UArrayModelsTest::testComputeSum()
		$this->markTestIncomplete ( "computeSum test not implemented" );

		UArrayModels::computeSum(/* parameters */);
	}

	/**
	 * Tests UArrayModels::removeAll()
	 */
	public function testRemoveAll() {
		// TODO Auto-generated UArrayModelsTest::testRemoveAll()
		$this->markTestIncomplete ( "removeAll test not implemented" );

		UArrayModels::removeAll(/* parameters */);
	}

	/**
	 * Tests UArrayModels::asArray()
	 */
	public function testAsArray() {
		// TODO Auto-generated UArrayModelsTest::testAsArray()
		$this->markTestIncomplete ( "asArray test not implemented" );

		UArrayModels::asArray(/* parameters */);
	}

	/**
	 * Tests UArrayModels::asJson()
	 */
	public function testAsJson() {
		// TODO Auto-generated UArrayModelsTest::testAsJson()
		$this->markTestIncomplete ( "asJson test not implemented" );

		UArrayModels::asJson(/* parameters */);
	}

	/**
	 * Tests UArrayModels::asArrayProperties()
	 */
	public function testAsArrayProperties() {
		// TODO Auto-generated UArrayModelsTest::testAsArrayProperties()
		$this->markTestIncomplete ( "asArrayProperties test not implemented" );

		UArrayModels::asArrayProperties(/* parameters */);
	}

	/**
	 * Tests UArrayModels::asJsonProperties()
	 */
	public function testAsJsonProperties() {
		// TODO Auto-generated UArrayModelsTest::testAsJsonProperties()
		$this->markTestIncomplete ( "asJsonProperties test not implemented" );

		UArrayModels::asJsonProperties(/* parameters */);
	}
}

