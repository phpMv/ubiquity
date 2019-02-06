<?php
use Ubiquity\db\Database;
use Ubiquity\contents\validation\ValidatorsManager;
use models\User;
use Ubiquity\contents\validation\validators\multiples\IdValidator;
use Ubiquity\contents\validation\validators\ConstraintViolation;

/**
 * ValidatorsManager test case.
 */
class ValidatorsManagerTest extends BaseTest {

	/**
	 *
	 * @var ValidatorsManager
	 */
	private $validatorsManager;
	protected $dbType;
	protected $dbName;
	/**
	 *
	 * @var Database
	 */
	private $database;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$db = $this->config ["database"];
		$this->dbType = $db ['type'];
		$this->dbName = $db ['dbName'];
		$this->database = new Database ( $this->dbType, $this->dbName, $this->db_server );
		ValidatorsManager::start ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->database = null;
	}

	protected function beforeQuery() {
		if (! $this->database->isConnected ())
			$this->database->connect ();
	}

	/**
	 * Tests ValidatorsManager::validate()
	 */
	public function testValidate() {
		$user = new User ();
		$result = ValidatorsManager::validate ( $user );
		$this->assertEquals ( 4, sizeof ( $result ) );
		$first = current ( $result );
		$this->assertTrue ( $first instanceof ConstraintViolation );
		$this->assertEquals ( 3, sizeof ( $first->getMessage () ) );
		$this->assertEquals ( IdValidator::class, $first->getValidatorType () );
	}

	/**
	 * Tests ValidatorsManager::validateInstances()
	 */
	public function testValidateInstances() {
		// TODO Auto-generated ValidatorsManagerTest::testValidateInstances()
		$this->markTestIncomplete ( "validateInstances test not implemented" );

		ValidatorsManager::validateInstances(/* parameters */);
	}

	/**
	 * Tests ValidatorsManager::clearCache()
	 */
	public function testClearCache() {
		// TODO Auto-generated ValidatorsManagerTest::testClearCache()
		$this->markTestIncomplete ( "clearCache test not implemented" );

		ValidatorsManager::clearCache(/* parameters */);
	}

	/**
	 * Tests ValidatorsManager::initCacheInstanceValidators()
	 */
	public function testInitCacheInstanceValidators() {
		// TODO Auto-generated ValidatorsManagerTest::testInitCacheInstanceValidators()
		$this->markTestIncomplete ( "initCacheInstanceValidators test not implemented" );

		ValidatorsManager::initCacheInstanceValidators(/* parameters */);
	}
}

