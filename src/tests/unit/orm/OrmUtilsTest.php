<?php
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;
use models\Organization;

/**
 * OrmUtils test case.
 */
class OrmUtilsTest extends BaseTest {
	private $dao;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->_startCache ();
		$this->dao = new DAO ();
		$this->_startDatabase ( $this->dao );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		parent::_after ();
		$this->dao = null;
	}

	public static function assertArrayCompare($result, $expected, bool $checkValues = false) {
		if ($checkValues) {
			self::assertTrue ( empty ( array_diff_assoc ( $expected, $result ) ) && empty ( array_diff_assoc ( $result, $expected ) ) );
		} else {
			self::assertTrue ( empty ( array_diff_key ( $expected, $result ) ) && empty ( array_diff_key ( $result, $expected ) ) );
		}
	}

	/**
	 * Tests OrmUtils::getKeyPropsAndValues_()
	 */
	public function testGetKeyPropsAndValues_() {
		$orga = $this->dao->getById ( Organization::class, 1 );
		$prop = new ReflectionProperty ( Organization::class, 'id' );
		$prop->setAccessible ( true );
		$ids = OrmUtils::getKeyPropsAndValues_ ( $orga, [ $prop ] );
		self::assertArrayCompare ( $ids, [ 'id' => 1 ] );
	}

	/**
	 * Tests OrmUtils::getMembers()
	 */
	public function testGetMembers() {
		// TODO Auto-generated OrmUtilsTest::testGetMembers()
		$this->markTestIncomplete ( "getMembers test not implemented" );

		OrmUtils::getMembers(/* parameters */);
	}

	/**
	 * Tests OrmUtils::getMembersAndValues()
	 */
	public function testGetMembersAndValues() {
		$orga = $this->dao->getById ( Organization::class, 1 );
		$memberValues = OrmUtils::getMembersAndValues ( $orga );
		self::assertArrayCompare ( $memberValues, [ 'id' => 1,'name' => 'Conservatoire National des Arts et Métiers','domain' => 'lecnam.net','aliases' => 'cnam-basse-normandie.fr;' ] );
	}

	/**
	 * Tests OrmUtils::isNotNullOrNullAccepted()
	 */
	public function testIsNotNullOrNullAccepted() {
		// TODO Auto-generated OrmUtilsTest::testIsNotNullOrNullAccepted()
		$this->markTestIncomplete ( "isNotNullOrNullAccepted test not implemented" );

		OrmUtils::isNotNullOrNullAccepted(/* parameters */);
	}

	/**
	 * Tests OrmUtils::getFirstKeyValue()
	 */
	public function testGetFirstKeyValue() {
		// TODO Auto-generated OrmUtilsTest::testGetFirstKeyValue()
		$this->markTestIncomplete ( "getFirstKeyValue test not implemented" );

		OrmUtils::getFirstKeyValue(/* parameters */);
	}

	/**
	 * Tests OrmUtils::getFirstKeyValue_()
	 */
	public function testGetFirstKeyValue_() {
		$orga = $this->dao->getById ( Organization::class, 1 );
		$id = OrmUtils::getFirstKeyValue_ ( $orga, [ 'id' ] );
		$this->assertEquals ( "1", $id );
	}

	/**
	 * Tests OrmUtils::getKeyValues()
	 */
	public function testGetKeyValues() {
		// TODO Auto-generated OrmUtilsTest::testGetKeyValues()
		$this->markTestIncomplete ( "getKeyValues test not implemented" );

		OrmUtils::getKeyValues(/* parameters */);
	}

	/**
	 * Tests OrmUtils::getPropKeyValues()
	 */
	public function testGetPropKeyValues() {
		// TODO Auto-generated OrmUtilsTest::testGetPropKeyValues()
		$this->markTestIncomplete ( "getPropKeyValues test not implemented" );

		OrmUtils::getPropKeyValues(/* parameters */);
	}

	/**
	 * Tests OrmUtils::getMembersWithAnnotation()
	 */
	public function testGetMembersWithAnnotation() {
		// TODO Auto-generated OrmUtilsTest::testGetMembersWithAnnotation()
		$this->markTestIncomplete ( "getMembersWithAnnotation test not implemented" );

		OrmUtils::getMembersWithAnnotation(/* parameters */);
	}

	/**
	 * Tests OrmUtils::exists()
	 */
	public function testExists() {
		$orga = $this->dao->getById ( Organization::class, 1 );
		$orgas = $this->dao->getAll ( Organization::class, '', false );
		$this->assertTrue ( OrmUtils::exists ( $orga, 'id', $orgas ) );
	}

	/**
	 * Tests OrmUtils::getAnnotationInfo()
	 */
	public function testGetAnnotationInfo() {
		// TODO Auto-generated OrmUtilsTest::testGetAnnotationInfo()
		$this->markTestIncomplete ( "getAnnotationInfo test not implemented" );

		OrmUtils::getAnnotationInfo(/* parameters */);
	}

	/**
	 * Tests OrmUtils::getAnnotationInfoMember()
	 */
	public function testGetAnnotationInfoMember() {
		// TODO Auto-generated OrmUtilsTest::testGetAnnotationInfoMember()
		$this->markTestIncomplete ( "getAnnotationInfoMember test not implemented" );

		OrmUtils::getAnnotationInfoMember(/* parameters */);
	}

	/**
	 * Tests OrmUtils::setFieldToMemberNames()
	 */
	public function testSetFieldToMemberNames() {
		// TODO Auto-generated OrmUtilsTest::testSetFieldToMemberNames()
		$this->markTestIncomplete ( "setFieldToMemberNames test not implemented" );

		OrmUtils::setFieldToMemberNames(/* parameters */);
	}

	/**
	 * Tests OrmUtils::objectAsJSON()
	 */
	public function testObjectAsJSON() {
		// TODO Auto-generated OrmUtilsTest::testObjectAsJSON()
		$this->markTestIncomplete ( "objectAsJSON test not implemented" );

		OrmUtils::objectAsJSON(/* parameters */);
	}

	/**
	 * Tests OrmUtils::getTransformers()
	 */
	public function testGetTransformers() {
		$transfs = array ("toView" => array ("name" => "Ubiquity\\contents\\transformation\\transformers\\UpperCase" ) );
		$this->assertArrayCompare ( $transfs, OrmUtils::getTransformers ( Organization::class ) );
	}

	/**
	 * Tests OrmUtils::getAccessors()
	 */
	public function testGetAccessors() {
		$transfs = array ("id" => "setId","name" => "setName","domain" => "setDomain","aliases" => "setAliases","groupes" => "setGroupes","organizationsettingss" => "setOrganizationsettingss","users" => "setUsers" );
		$this->assertArrayCompare ( $transfs, OrmUtils::getAccessors ( Organization::class ) );
	}

	/**
	 * Tests OrmUtils::clearMetaDatas()
	 */
	public function testClearMetaDatas() {
		// TODO Auto-generated OrmUtilsTest::testClearMetaDatas()
		$this->markTestIncomplete ( "clearMetaDatas test not implemented" );

		OrmUtils::clearMetaDatas(/* parameters */);
	}
}

