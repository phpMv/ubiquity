<?php
use Ubiquity\controllers\Startup;
use controllers\RestApiController;
use Ubiquity\utils\base\UString;
use Ubiquity\events\EventsManager;
use Ubiquity\translation\TranslatorManager;

/**
 * Startup test case.
 */
class RestApiTest extends BaseTest {

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
		$this->_startServices ( true );
		$this->startup = new Startup ();
		EventsManager::start ();
		TranslatorManager::start ( 'fr_FR', 'en' );
		$this->_initRequest ( 'RestApiController', 'GET' );
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
		$result = $this->_display ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( RestApiController::class, $this->startup->getController () );
			$this->assertEquals ( 'index', $this->startup->getAction () );
			$this->assertEquals ( 0, sizeof ( $this->startup->getActionParams () ) );
		} );
		$this->assertTrue ( UString::contains ( 'Conservatoire National des Arts', $result ) );
	}

	public function testNormalizationDatas() {
		$this->_initRequest ( 'RestApiController/testNormalizationDatas', 'GET' );
		$result = $this->_display ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( RestApiController::class, $this->startup->getController () );
			$this->assertEquals ( 'testNormalizationDatas', $this->startup->getAction () );
			$this->assertEquals ( 0, sizeof ( $this->startup->getActionParams () ) );
		} );
		$this->assertTrue ( UString::contains ( 'Conservatoire National des Arts', $result ) );
	}

	public function testNormalizationData() {
		$this->_initRequest ( 'RestApiController/testNormalizationData', 'GET' );
		$result = $this->_display ( function () {
			$this->startup->run ( $this->config );
			$this->assertEquals ( RestApiController::class, $this->startup->getController () );
			$this->assertEquals ( 'testNormalizationData', $this->startup->getAction () );
			$this->assertEquals ( 0, sizeof ( $this->startup->getActionParams () ) );
		} );
		$this->assertTrue ( UString::contains ( 'Conservatoire National des Arts', $result ) );
	}
}

