<?php
use Ubiquity\controllers\Startup;
use controllers\TestController;
use services\Service;
use controllers\TestSimpleViewController;

/**
 * Controller test case.
 */
class ControllerTest extends BaseTest {

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

	protected function _assertDisplayContains($callback, $result) {
		$res = $this->_display ( $callback );
		if (\is_array ( $result )) {
			foreach ( $result as $c ) {
				$this->assertStringContainsString ( $c, $res );
			}
		} else {
			$this->assertStringContainsString ( $result, $res );
		}
	}

	/**
	 * Tests Controller::loadDefaultView()
	 */
	public function testLoadDefaultView() {
		$_GET ["c"] = "route/test/withView/avec vue";
		$this->_assertDisplayContains ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestController::class, $this->startup->getController () );
			$this->assertEquals ( 'withView', $this->startup->getAction () );
		}, 'avec vue' );
	}

	/**
	 * Tests Controller::redirectToWithView()
	 */
	public function testRedirectToWithView() {
		$_GET ["c"] = "TestController/redirectToWithView";
		$this->_assertDisplayContains ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestController::class, $this->startup->getController () );
			$this->assertEquals ( 'withView', $this->startup->getAction () );
		}, 'redirection' );
	}

	/**
	 * Tests Controller::forward()
	 */
	public function testForward() {
		$_GET ["c"] = "TestController/forwardToWithView";
		$this->_assertDisplayContains ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestController::class, $this->startup->getController () );
			$this->assertEquals ( 'withView', $this->startup->getAction () );
		}, 'redirection2' );
	}

	/**
	 * Tests loadAssets()
	 */
	public function testLoadAssets() {
		$_GET ["c"] = "route/test/assets/Hello world!";
		$this->_assertDisplayContains ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestController::class, $this->startup->getController () );
			$this->assertEquals ( 'assets', $this->startup->getAction () );
		}, [ 'Hello world!','assets/semantic/css/style.css','new content' ] );
	}

	/**
	 * Tests SimpleViewController::index()
	 */
	public function testSimpleIndex() {
		$_GET ["c"] = "route/simple/index";
		$this->_assertDisplayContains ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestSimpleViewController::class, $this->startup->getController () );
			$this->assertEquals ( 'index', $this->startup->getAction () );
		}, 'Hello world!');
	}

	/**
	 * Tests SimpleViewController::withView()
	 */
	public function testSimpleWithView() {
		$_GET ["c"] = "route/simple/withView/Hello";
		$this->_assertDisplayContains ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestSimpleViewController::class, $this->startup->getController () );
			$this->assertEquals ( 'withView', $this->startup->getAction () );
		}, 'Hello');
	}

	/**
	 * Tests SimpleViewController::withViewString()
	 */
	public function testSimpleWithViewString() {
		$_GET ["c"] = "route/simple/withViewString/Hello";
		$this->_assertDisplayContains ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestSimpleViewController::class, $this->startup->getController () );
			$this->assertEquals ( 'withViewString', $this->startup->getAction () );
		}, 'Hello');
	}

	protected function getCacheDirectory() {
		return null;
	}
}

