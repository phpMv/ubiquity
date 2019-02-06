<?php
use Ubiquity\db\Database;
use Ubiquity\contents\validation\ValidatorsManager;
use models\User;
use Ubiquity\contents\validation\validators\multiples\IdValidator;
use Ubiquity\contents\validation\validators\ConstraintViolation;
use Ubiquity\orm\DAO;
use models\Organization;
use Ubiquity\cache\CacheManager;
use models\Groupe;
use Ubiquity\orm\creator\database\DbModelsCreator;

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
		$orgas = DAO::getAll ( Organization::class, '', false );
		$result = ValidatorsManager::validateInstances ( $orgas );
		if (sizeof ( $result ) != 0) {
			$violation = current ( $result );
			$this->assertTrue ( $violation instanceof ConstraintViolation );
			$this->assertEquals ( "This value should not be null", $violation->getMessage () );
			$this->assertEquals ( "domain", $violation->getMember () );
		}
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

	/**
	 * Tests ValidationModelGenerator::__construct()
	 */
	public function testValidationModelGenerator() {
		$this->config ["cache"] ["directory"] = "new-cache/";
		(new DbModelsCreator ())->create ( $this->config, false );
		CacheManager::$cache = null;
		CacheManager::start ( $this->config );

		CacheManager::initModelsCache ( $this->config );
		ValidatorsManager::start ();
		$groupes = DAO::getAll ( Groupe::class, '', false );
		$result = ValidatorsManager::validateInstances ( $groupes );
		$this->assertEquals ( sizeof ( $result ), 9 );
	}
}

