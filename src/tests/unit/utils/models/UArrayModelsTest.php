<?php

use Ubiquity\utils\models\UArrayModels;
use Ubiquity\orm\DAO;
use models\Organization;
use models\Groupe;
use models\User;

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
		$groupes=$this->dao->getAll(Groupe::class,'1=1',['organization']);
		$gr=UArrayModels::groupsBy($groupes,[fn($item)=>$item->getOrganization()->getName()],null,true);
		$this->assertEquals(3,\count($gr));
	}

	/**
	 * Tests UArrayModels::groupBy()
	 */
	public function testGroupBy() {
		$groupes=$this->dao->getAll(Groupe::class,'1=1',['organization']);
		$gr=UArrayModels::groupBy($groupes,fn($item)=>$item->getOrganization()->getName(),null,true);
		$this->assertEquals(3,\count($gr));
		$gr=UArrayModels::groupBy($groupes,fn($item)=>$item->getOrganization()->getName(),fn($item)=>$item->getName(),true);
		$this->assertEquals(3,\count($gr));
	}

	/**
	 * Tests UArrayModels::asKeyValues()
	 */
	public function testAsKeyValues() {
		$orgas=$this->dao->getAll(Organization::class);
		$array=UArrayModels::asKeyValues($orgas,'getId');
		$this->assertEquals(4,\count($array));
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
		$this->assertTrue(UArrayModels::containsBy($orgas,\current($orgas)));
	}

	/**
	 * Tests UArrayModels::containsById()
	 */
	public function testContainsById() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertTrue(UArrayModels::containsById($orgas,\current($orgas)));
	}

	/**
	 * Tests UArrayModels::remove()
	 */
	public function testRemove() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertEquals(4,\count($orgas));
		$orgas=UArrayModels::remove($orgas,fn($item)=>$item->getId()==1);
		$this->assertEquals(3,\count($orgas));
	}

	/**
	 * Tests UArrayModels::removeBy()
	 */
	public function testRemoveBy() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertEquals(4,\count($orgas));
		$orgas=UArrayModels::removeBy($orgas,\current($orgas));
		$this->assertEquals(3,\count($orgas));
	}

	/**
	 * Tests UArrayModels::removeAllBy()
	 */
	public function testRemoveAllBy() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertEquals(4,\count($orgas));
		$orgas=UArrayModels::removeAllBy($orgas,\current($orgas));
		$this->assertEquals(3,\count($orgas));
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
		$users=$this->dao->getAll(User::class);
		$this->assertEquals(4,UArrayModels::computeSumProperty($users,'suspended'));
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
		$groupes=$this->dao->getAll(Groupe::class,'1=1',['organization']);
		$this->assertEquals(11,\count($groupes));
		$groupes=UArrayModels::removeAll($groupes,fn($item)=>$item->getOrganization()->getId()==1);
		$this->assertEquals(8,\count($groupes));
	}

	/**
	 * Tests UArrayModels::asArray()
	 */
	public function testAsArray() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertEquals(4,\count($orgas));
		$array=UArrayModels::asArray($orgas);
		$this->assertEquals(4,\count($array));
	}

	/**
	 * Tests UArrayModels::asJson()
	 */
	public function testAsJson() {
		$orgas=$this->dao->getAll(Organization::class,'1=1',false);
		$this->assertEquals(4,\count($orgas));
		$json=UArrayModels::asJson($orgas);
		$this->assertJson($json);
	}

	/**
	 * Tests UArrayModels::asArrayProperties()
	 */
	public function testAsArrayProperties() {
		$orgas=$this->dao->getAll(Organization::class);
		$this->assertEquals(4,\count($orgas));
		$array=UArrayModels::asArrayProperties($orgas,['id','name']);
		$this->assertEquals(4,\count($array));
	}

	/**
	 * Tests UArrayModels::asJsonProperties()
	 */
	public function testAsJsonProperties() {
		$orgas=$this->dao->getAll(Organization::class,'1=1',false);
		$this->assertEquals(4,\count($orgas));
		$json=UArrayModels::asJsonProperties($orgas,['id','name']);
		$this->assertJson($json);
	}
}

