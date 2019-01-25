<?php
use Ubiquity\controllers\Startup;
use tests\unit\controllers\controllers\TestController;

require_once 'Ubiquity/controllers/Startup.php';
require_once 'tests/unit/controllers/controllers/TestController.php';
require_once 'tests/unit/controllers/controllers/TestControllerInitialize.php';

/**
 * Startup test case.
 */
class StartupTest extends \Codeception\Test\Unit {

	/**
	 *
	 * @var Startup
	 */
	private $startup;
	private $config;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		$this->config = include 'tests/unit/config/config.php';
		include 'tests/unit/config/services.php';
		$this->startup = new Startup ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->startup = null;
	}

	/**
	 * Tests Startup::run()
	 */
	public function testRun() {
		ob_start ();
		Startup::run ( $this->config );
		$this->assertEquals ( TestController::class, $this->startup->getController () );
		$this->assertEquals ( 'index', $this->startup->getAction () );
		$this->assertNull ( $this->startup->getActionParams () );
		$res = ob_get_clean ();
		$this->assertEquals ( 'Hello world!', $res );
	}

	/**
	 * Tests Startup::forward()
	 */
	public function testForward() {
		ob_start ();
		Startup::forward ( "TestController/doForward" );
		$res = ob_get_clean ();
		$this->assertEquals ( 'forward!', $res );
		ob_start ();
		Startup::forward ( "TestControllerInitialize/doForward" );
		$res = ob_get_clean ();
		$this->assertEquals ( 'initialize!-forward!-finalize!', $res );
	}

	/**
	 * Tests Startup::runAction()
	 */
	public function testRunAction() {
		// TODO Auto-generated StartupTest::testRunAction()
		$this->markTestIncomplete ( "runAction test not implemented" );

		Startup::runAction(/* parameters */);
	}

	/**
	 * Tests Startup::injectDependences()
	 */
	public function testInjectDependences() {
		// TODO Auto-generated StartupTest::testInjectDependences()
		$this->markTestIncomplete ( "injectDependences test not implemented" );

		Startup::injectDependences(/* parameters */);
	}

	/**
	 * Tests Startup::runAsString()
	 */
	public function testRunAsString() {
		// TODO Auto-generated StartupTest::testRunAsString()
		$this->markTestIncomplete ( "runAsString test not implemented" );

		Startup::runAsString(/* parameters */);
	}

	/**
	 * Tests Startup::errorHandler()
	 */
	public function testErrorHandler() {
		// TODO Auto-generated StartupTest::testErrorHandler()
		$this->markTestIncomplete ( "errorHandler test not implemented" );

		Startup::errorHandler(/* parameters */);
	}

	/**
	 * Tests Startup::getController()
	 */
	public function testGetController() {
		// TODO Auto-generated StartupTest::testGetController()
		$this->markTestIncomplete ( "getController test not implemented" );

		Startup::getController(/* parameters */);
	}

	/**
	 * Tests Startup::getControllerSimpleName()
	 */
	public function testGetControllerSimpleName() {
		// TODO Auto-generated StartupTest::testGetControllerSimpleName()
		$this->markTestIncomplete ( "getControllerSimpleName test not implemented" );

		Startup::getControllerSimpleName(/* parameters */);
	}

	/**
	 * Tests Startup::getViewNameFileExtension()
	 */
	public function testGetViewNameFileExtension() {
		// TODO Auto-generated StartupTest::testGetViewNameFileExtension()
		$this->markTestIncomplete ( "getViewNameFileExtension test not implemented" );

		Startup::getViewNameFileExtension(/* parameters */);
	}

	/**
	 * Tests Startup::getAction()
	 */
	public function testGetAction() {
		// TODO Auto-generated StartupTest::testGetAction()
		$this->markTestIncomplete ( "getAction test not implemented" );

		Startup::getAction(/* parameters */);
	}

	/**
	 * Tests Startup::getActionParams()
	 */
	public function testGetActionParams() {
		// TODO Auto-generated StartupTest::testGetActionParams()
		$this->markTestIncomplete ( "getActionParams test not implemented" );

		Startup::getActionParams(/* parameters */);
	}

	/**
	 * Tests Startup::getFrameworkDir()
	 */
	public function testGetFrameworkDir() {
		// TODO Auto-generated StartupTest::testGetFrameworkDir()
		$this->markTestIncomplete ( "getFrameworkDir test not implemented" );

		Startup::getFrameworkDir(/* parameters */);
	}

	/**
	 * Tests Startup::getApplicationDir()
	 */
	public function testGetApplicationDir() {
		// TODO Auto-generated StartupTest::testGetApplicationDir()
		$this->markTestIncomplete ( "getApplicationDir test not implemented" );

		Startup::getApplicationDir(/* parameters */);
	}

	/**
	 * Tests Startup::getApplicationName()
	 */
	public function testGetApplicationName() {
		// TODO Auto-generated StartupTest::testGetApplicationName()
		$this->markTestIncomplete ( "getApplicationName test not implemented" );

		Startup::getApplicationName(/* parameters */);
	}
}

