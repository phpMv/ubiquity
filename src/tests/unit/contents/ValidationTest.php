<?php
use Ubiquity\controllers\Startup;
use Ubiquity\db\Database;
use Ubiquity\contents\validation\ValidatorsManager;
use models\User;
use Ubiquity\contents\validation\validators\ConstraintViolation;
use Ubiquity\contents\validation\validators\multiples\IdValidator;
class ValidationTest extends \BaseTest {
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

	// Test
	public function validateAUserTest() {
		$user = new User ();
		$result = ValidatorsManager::validate ( $user );
		$this->assertEquals ( 4, sizeof ( $result ) );
		$first = current ( $result );
		$this->assertTrue ( $first instanceof ConstraintViolation );
		$this->assertEquals ( 3, sizeof ( $first->getMessage () ) );
		$this->assertEquals ( IdValidator::class, $first->getValidatorType () );
	}
}

