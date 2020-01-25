<?php
use Ubiquity\controllers\Startup;
use Ubiquity\utils\http\USession;
use Ubiquity\exceptions\RestException;
use controllers\TestController;
use controllers\TestControllerWithControl;
use controllers\TestRestController;
use controllers\TestControllerInitialize;
use services\Service;

/**
 * Startup test case.
 */
class StartupTest extends BaseTest {

	/**
	 *
	 * @var Startup
	 */
	private $startup;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->_startServices ();
		$this->startup = new Startup ();
		$this->_initRequest ( 'TestController', 'GET' );
	}

	protected function _startServices($what = false) {
		$this->_startCache ();
		$this->_startRouter ( $what );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->startup = null;
	}

	protected function _display($callback) {
		ob_start ();
		$callback ();
		return ob_get_clean ();
	}

	protected function _assertDisplayEquals($callback, $result) {
		$res = $this->_display ( $callback );
		$this->assertEquals ( $result, $res );
	}

	/**
	 * Tests Startup::run()
	 */
	public function testRun() {
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestController::class, $this->startup->getController () );
			$this->assertEquals ( 'index', $this->startup->getAction () );
			$this->assertEquals ( 0, sizeof ( $this->startup->getActionParams () ) );
		}, 'service init!Hello world!' );
		// With routes
		$_GET ["c"] = "route/test/index";
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestControllerWithControl::class, $this->startup->getController () );
			$this->assertEquals ( 'index', $this->startup->getAction () );
		}, 'service init!initialize!-Hello world!-finalize!' );
		$_GET ["c"] = "route/test/";
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestControllerWithControl::class, $this->startup->getController () );
			$this->assertEquals ( 'index', $this->startup->getAction () );
		}, 'service init!initialize!-Hello world!-finalize!' );
		$_GET ["c"] = "route/test";
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestControllerWithControl::class, $this->startup->getController () );
			$this->assertEquals ( 'index', $this->startup->getAction () );
		}, 'service init!initialize!-Hello world!-finalize!' );
		$_GET ["c"] = "route/test/ctrl";
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestControllerWithControl::class, $this->startup->getController () );
			$this->assertEquals ( 'actionWithControl', $this->startup->getAction () );
		}, 'service init!invalid!' );
		$_GET ["c"] = "route/test/ctrl/";
		USession::set ( 'user', 'user' );
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestControllerWithControl::class, $this->startup->getController () );
			$this->assertEquals ( 'actionWithControl', $this->startup->getAction () );
		}, 'service init!initialize!-authorized!-finalize!' );
		// Route with params
		$_GET ["c"] = "route/test/params/aa/bb";
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestControllerWithControl::class, $this->startup->getController () );
			$this->assertEquals ( 'withParams', $this->startup->getAction () );
			$this->assertEquals ( [ 'aa','bb' ], $this->startup->getActionParams () );
		}, 'service init!initialize!-aa-bb!-finalize!' );
		$_GET ["c"] = "route/test/params/aa/";
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestControllerWithControl::class, $this->startup->getController () );
			$this->assertEquals ( 'withParams', $this->startup->getAction () );
			$this->assertEquals ( [ 'aa' ], $this->startup->getActionParams () );
		}, 'service init!initialize!-aa-default!-finalize!' );
		// Rest
		$this->_startServices ( 'rest' );
		$_GET ["c"] = "rest/test";
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestRestController::class, $this->startup->getController () );
			$this->assertEquals ( 'index', $this->startup->getAction () );
			$this->assertEquals ( 0, sizeof ( $this->startup->getActionParams () ) );
		}, 'service init!{"test":"ok"}' );
		$_GET ["c"] = "rest/test/ticket";
		$this->expectException ( RestException::class );
		try {

			$this->_assertDisplayEquals ( function () {
				$this->startup->run ( $this->config );
			}, '' );
		} finally{
			ob_get_clean ();
		}
	}

	/**
	 * Tests Startup::forward()
	 */
	public function testForward() {
		$this->_assertDisplayEquals ( function () {
			$this->startup->forward ( "TestController/doForward" );
		}, 'service init!forward!' );
		$this->_assertDisplayEquals ( function () {
			$this->startup->forward ( "TestControllerInitialize/doForward" );
		}, 'service init!initialize!-forward!-finalize!' );
		$this->_assertDisplayEquals ( function () {
			$this->startup->forward ( "TestControllerWithControl/validAction" );
		}, 'service init!initialize!-valid action!-finalize!' );
		$this->_assertDisplayEquals ( function () {
			$this->startup->forward ( "TestControllerWithControl/validAction" );
		}, 'service init!initialize!-valid action!-finalize!' );
		$this->_assertDisplayEquals ( function () {
			$this->startup->forward ( "TestControllerWithControl/actionWithControl" );
		}, 'service init!invalid!' );
		USession::set ( 'user', 'user' );
		$this->_assertDisplayEquals ( function () {
			$this->startup->forward ( "TestControllerWithControl/actionWithControl" );
		}, 'service init!initialize!-authorized!-finalize!' );
	}

	/**
	 * Tests Startup::injectDependences()
	 */
	public function testInjectDependences() {
		$ctrl = new TestController ();
		$this->startup->injectDependences ( $ctrl );
		$this->assertTrue ( property_exists ( $ctrl, 'injected' ) );
		$this->assertInstanceOf ( Service::class, $ctrl->injected );
	}

	/**
	 * Tests Startup::runAsString()
	 */
	public function testRunAsString() {
		$u = [ TestControllerInitialize::class,"index" ];
		$this->assertEquals ( 'service init!initialize!-Hello world!-finalize!', $this->startup->runAsString ( $u ) );
		$this->assertEquals ( 'service init!initialize!-Hello world!', $this->startup->runAsString ( $u, true, false ) );
		$this->assertEquals ( 'service init!Hello world!', $this->startup->runAsString ( $u, false, false ) );
	}

	/**
	 * Tests Startup::getControllerSimpleName()
	 */
	public function testGetControllerSimpleName() {
		$this->startup->run ( $this->config );
		$this->assertEquals ( 'TestController', $this->startup->getControllerSimpleName () );
		$_GET ["c"] = "route/test/ctrl/";
		$this->startup->run ( $this->config );
		$this->assertEquals ( 'TestControllerWithControl', $this->startup->getControllerSimpleName () );
	}

	/**
	 * Tests Startup::getViewNameFileExtension()
	 */
	public function testGetViewNameFileExtension() {
		return $this->assertEquals ( 'html', $this->startup->getViewNameFileExtension () );
	}

	/**
	 * Tests Startup::getAction()
	 */
	public function testGetAction() {
		$this->startup->run ( $this->config );
		$this->assertEquals ( 'index', $this->startup->getAction () );
		$_GET ["c"] = "route/test/ctrl/";
		$this->startup->run ( $this->config );
		$this->assertEquals ( 'actionWithControl', $this->startup->getAction () );
	}

	/**
	 * Tests Startup::getActionParams()
	 */
	public function testGetActionParams() {
		$this->startup->run ( $this->config );
		$this->assertEquals ( 0, sizeof ( $this->startup->getActionParams () ) );

		$_GET ["c"] = "route/test/ctrl/";
		$this->startup->run ( $this->config );
		$this->assertEquals ( 0, sizeof ( $this->startup->getActionParams () ) );

		$_GET ["c"] = "/route/test/params/aa";
		$this->startup->run ( $this->config );
		$this->assertEquals ( 1, sizeof ( $this->startup->getActionParams () ) );
		$this->assertEquals ( 'aa', $this->startup->getActionParams () [0] );

		$_GET ["c"] = "/route/test/params/aa/bb";
		$this->startup->run ( $this->config );
		$this->assertEquals ( 2, sizeof ( $this->startup->getActionParams () ) );
		$this->assertEquals ( 'aa', $this->startup->getActionParams () [0] );
		$this->assertEquals ( 'bb', $this->startup->getActionParams () [1] );
	}

	/**
	 * Tests Startup::getFrameworkDir()
	 */
	public function testGetFrameworkDir() {
		$this->assertDirectoryExists ( $this->startup->getFrameworkDir () );
	}

	/**
	 * Tests Startup::getApplicationDir()
	 */
	public function testGetApplicationDir() {
		$this->assertDirectoryExists ( $this->startup->getApplicationDir () );
	}

	/**
	 * Tests Startup::getApplicationName()
	 */
	public function testGetApplicationName() {
		$this->assertNotEmpty ( $this->startup->getApplicationName () );
	}
}

