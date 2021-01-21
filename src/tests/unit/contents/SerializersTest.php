<?php
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UString;
use models\Groupe;
use models\User;
use Ubiquity\contents\serializers\PhpSerializer;
use Ubiquity\contents\serializers\JsonSerializer;

/**
 * Serializers test case.
 */
class SerializersTest extends \Codeception\Test\Unit {
	private $groupe;
	private $php;
	private $json;
	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		$this->php=new PhpSerializer();
		$this->json=new JsonSerializer();
		$this->groupe=new Groupe();
		$this->groupe->setName('Sup');
		$this->groupe->setId(1);
		$this->groupe->setAliases('aliases');
		$this->groupe->setEmail('email@mail.com');
		$user=new User();
		$user->setFirstname('joe');
		$this->groupe->setUsers([$user]);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
	}

	/**
	 * Tests PhpSerializer::serialize()
	 */
	public function testPhpSerializer() {
		$serial=$this->php->serialize($this->groupe);
		$groupe=$this->php->unserialize($serial);
		$this->assertInstanceOf( Groupe::class,$groupe );
		$this->assertEquals ( 'Sup',$groupe->getName() );
		$this->assertEquals ( 'aliases',$groupe->getAliases() );
		$this->assertEquals ( 1,$groupe->getId() );
		$this->assertIsArray($groupe->getUsers());
		$user=\current($groupe->getUsers());
		$this->assertEquals ( 'joe',$user->getFirstname() );
	}

}

