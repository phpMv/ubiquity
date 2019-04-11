<?php
use models\User;
use models\Connection;
use Ubiquity\contents\transformation\TransformersManager;
use Ubiquity\orm\DAO;
use Ubiquity\controllers\Startup;
use Ubiquity\orm\OrmUtils;

/**
 * TransformersManager test case.
 */
class TransformersManagerTest extends BaseTest {

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		OrmUtils::clearMetaDatas ();
		$this->_loadConfig ();
		Startup::setConfig ( $this->config );
		$this->_startCache ();
		$db = $this->config ["database"] ?? [ ];
		if ($db ["dbName"] !== "") {
			DAO::connect ( $db ["type"] ?? 'mysql', $db ["dbName"], $db ["serverName"] ?? '127.0.0.1', $db ["port"] ?? 3306, $db ["user"] ?? 'root', $db ["password"] ?? '', $db ["options"] ?? [ ], $db ["cache"] ?? false);
		}
		TransformersManager::startProd ();
		DAO::$transformerOp = 'transform';
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->database = null;
		DAO::$transformerOp = 'toView';
		DAO::$useTransformers = false;
	}

	protected function _display($callback) {
		ob_start ();
		$callback ();
		return ob_get_clean ();
	}

	/**
	 * Tests Perso transformer
	 */
	public function testPerso() {
		$this->assertEquals ( $this->config ['cache'] ['directory'], 'cache-contents/' );
		$this->assertTrue ( DAO::$useTransformers );
		$metaDatas = OrmUtils::getModelMetadata ( User::class );
		$transformers = $metaDatas ["#transformers"] ['toView'] ?? [ ];
		$this->assertEquals ( 1, sizeof ( $transformers ) );
		$user = DAO::getOne ( User::class, 'id=1' );
		$password = $user->getPassword ();
		DAO::$transformerOp = 'toView';
		$user2 = DAO::getOne ( User::class, 'id=1' );
		$this->assertEquals ( sha1 ( $password ), $user2->getPassword () );
	}

	/**
	 * Tests DateTime transformer
	 */
	public function testDateTime() {
		$co = DAO::getOne ( Connection::class, 3 );
		$dt = $co->getDateCo ();
		$this->assertInstanceOf ( DateTime::class, $dt );
		DAO::$transformerOp = 'toView';
		$co = DAO::getOne ( Connection::class, 3 );
		$this->assertEquals ( $dt->format ( 'Y-m-d H:i:s' ), $co->getDateCo () );
		DAO::$transformerOp = 'toForm';
		$co = DAO::getOne ( Connection::class, 3 );
		$this->assertEquals ( $dt->format ( 'Y-m-d\TH:i:s' ), $co->getDateCo () );
	}

	public function testTransformFunctions() {
		DAO::$useTransformers = false;
		$co = DAO::getOne ( Connection::class, 3 );
		TransformersManager::transformInstance ( $co );
		$dt = $co->getDateCo ();
		$this->assertInstanceOf ( DateTime::class, $dt );
		$co = DAO::getOne ( Connection::class, 3 );
		TransformersManager::transform ( $co, 'dateCo' );
		$dt = $co->getDateCo ();
		$this->assertInstanceOf ( DateTime::class, $dt );
	}

	protected function getCacheDirectory() {
		return "cache-contents/";
	}
}

