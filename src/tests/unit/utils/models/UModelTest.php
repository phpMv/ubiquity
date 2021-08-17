<?php

use Ubiquity\orm\DAO;
use models\User;
use Ubiquity\utils\models\UModel;
use models\Groupe;
use models\Organization;

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
		$o=DAO::getById(Organization::class, 1,true);
		$users=$o->getUsers();
		$this->assertEquals(12,\count($users));
		UModel::removeFromByIndex($o, 'users',9);
		$this->assertEquals(11,\count($o->getUsers()));
	}
	
	public function testGetOneWithRelations() {
		$orga = DAO::getOne ( Organization::class, 'domain="lecnam.net"', true );
		$this->assertEquals ( "CONSERVATOIRE NATIONAL DES ARTS ET MéTIERS", $orga->getName () );
		$this->assertEquals(3,\count($orga->getGroupes()));
		
	}

	/**
	 * Tests UModel::asArray()
	 */
	public function testAsArray() {
		$o=$this->dao->getById(Organization::class, 1, false);
		$array=UModel::asArray($o);
		$this->assertTrue($array['id']==1);
		$this->assertTrue($array['name']=='CONSERVATOIRE NATIONAL DES ARTS ET MéTIERS');
	}

	/**
	 * Tests UModel::asJson()
	 */
	public function testAsJson() {
		$u=$this->dao->getById(User::class, 1,false);
		$json=UModel::asJson($u);
		$this->assertJson($json);
	}

	/**
	 * Tests UModel::asArrayProperties()
	 */
	public function testAsArrayProperties() {
		$o=$this->dao->getById(Organization::class, 1,false);
		$array=UModel::asArrayProperties($o,['id','name']);
		$this->assertTrue($array['id']==1);
		$this->assertTrue($array['name']=='CONSERVATOIRE NATIONAL DES ARTS ET MéTIERS');
	}

	/**
	 * Tests UModel::asJsonProperties()
	 */
	public function testAsJsonProperties() {
		$u=$this->dao->getById(User::class, 1);
		$json=UModel::asJsonProperties($u,['id','firstname']);
		$this->assertJson($json);
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

