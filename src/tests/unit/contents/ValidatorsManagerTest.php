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
use Ubiquity\controllers\Startup;
use services\TestClassToValidate;
use Ubiquity\contents\validation\validators\basic\IsBooleanValidator;
use Ubiquity\contents\validation\validators\basic\IsNullValidator;
use Ubiquity\contents\validation\validators\basic\NotEmptyValidator;
use Ubiquity\contents\validation\validators\basic\NotNullValidator;
use Ubiquity\contents\validation\validators\basic\IsFalseValidator;
use Ubiquity\contents\validation\validators\basic\IsTrueValidator;
use Ubiquity\contents\validation\validators\basic\TypeValidator;
use Ubiquity\contents\validation\validators\basic\IsEmptyValidator;

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

	protected function _display($callback) {
		ob_start ();
		$callback ();
		return ob_get_clean ();
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
		$this->config ["mvcNS"] = [ "models" => "models","controllers" => "controllers","rest" => "" ];
		Startup::setConfig ( $this->config );
		(new DbModelsCreator ())->create ( $this->config, false );
		CacheManager::$cache = null;
		CacheManager::start ( $this->config );

		CacheManager::initModelsCache ( $this->config );
		ValidatorsManager::start ();
		$groupes = DAO::getAll ( Groupe::class, '', false );
		$result = ValidatorsManager::validateInstances ( $groupes );
		$this->assertEquals ( sizeof ( $result ), 9 );
	}

	/**
	 * Tests base validators
	 */
	public function testValidatorsBase() {
		$object = new TestClassToValidate ();
		ValidatorsManager::addClassValidators ( TestClassToValidate::class );
		$res = ValidatorsManager::validate ( $object );
		$this->assertEquals ( 0, sizeof ( $res ) );

		$object->setBool ( "not boolean" );
		$res = ValidatorsManager::validate ( $object );
		$this->assertEquals ( 1, sizeof ( $res ) );
		$current = current ( $res );
		$this->assertInstanceOf ( ConstraintViolation::class, $current );
		$this->assertEquals ( "This value should be a boolean", $current->getMessage () );
		$this->assertEquals ( "not boolean", $current->getValue () );
		$this->assertEquals ( "bool", $current->getMember () );
		$this->assertEquals ( IsBooleanValidator::class, $current->getValidatorType () );
		$this->assertNull ( $current->getSeverity () );

		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setIsNull ( 'pas null' );
		}, IsNullValidator::class );
		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setNotEmpty ( '' );
		}, NotEmptyValidator::class );
		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setNotEmpty ( null );
		}, NotEmptyValidator::class );
		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setNotNull ( null );
		}, NotNullValidator::class );
		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setIsFalse ( true );
		}, IsFalseValidator::class );
		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setIsFalse ( "blop" );
		}, IsFalseValidator::class );
		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setIsTrue ( false );
		}, IsTrueValidator::class );
		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$user = new User ();
			$user->setEmail ( "email" );
			$object->setType ( $user );
		}, TypeValidator::class );

		$this->testValidatorInstanceOf ( function (TestClassToValidate $object) {
			$object->setIsEmpty ( "not empty" );
		}, IsEmptyValidator::class );
	}

	protected function testValidator($callback) {
		$object = new TestClassToValidate ();
		$callback ( $object );
		$res = ValidatorsManager::validate ( $object );
		$this->assertEquals ( 1, sizeof ( $res ) );
		return current ( $res );
	}

	protected function testValidatorInstanceOf($callback, $classValidator) {
		$constraint = $this->testValidator ( $callback );
		$this->assertEquals ( $classValidator, $constraint->getValidatorType () );
	}
}

