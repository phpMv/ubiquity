<?php
use Ubiquity\controllers\Startup;
use controllers\TestController;
use services\Service;

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
		$this->config ['di'] = [ ];

		$this->_startServices ();
		$this->startup = new Startup ();
		$this->startup->config = $this->config;
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
	 * Tests Controller::loadDefaultView()
	 */
	public function testLoadDefaultView() {
		$_GET ["c"] = "route/test/withView/avec vue";
		$this->_assertDisplayEquals ( function () {
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
		$this->_assertDisplayEquals ( function () {
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
		$this->_assertDisplayEquals ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( TestController::class, $this->startup->getController () );
			$this->assertEquals ( 'withView', $this->startup->getAction () );
		}, 'redirection2' );
	}

	protected function getCacheDirectory() {
		return null;
	}
}

