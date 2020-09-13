<?php
use Ubiquity\log\Logger;
use Ubiquity\controllers\Startup;
use Ubiquity\log\LoggerParams;
use Ubiquity\log\LogMessage;
use Ubiquity\log\libraries\UMonolog;

/**
 * Logger test case.
 */
class LoggerTest extends BaseTest {

	private $logger;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->logger=new UMonolog('tests');
		$this->config ["debug"] = true;
		$this->logger->init ( $this->config );
		$this->_startServices ();
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
		$this->logger->clearAll ();
		$this->config ["debug"] = false;
	}

	/**
	 * Tests Logger::warn()
	 */
	public function testNavigate() {
		$logs = $this->logger->asObjects ();
		$this->assertEquals ( 0, sizeof ( $logs ) );
		$this->_initRequest ( 'TestController', 'GET' );
		Startup::run ( $this->config );
		$logs = $this->logger->asObjects ( false, null, LoggerParams::ROUTER );
		$this->assertEquals ( 1, sizeof ( $logs ) );
		$this->assertInstanceOf ( LogMessage::class, $logs [0] );
	}

	/**
	 * Tests Logger::warn()
	 */
	public function testDatabase() {
		$logs = $this->logger->asObjects ();
		$this->assertEquals ( 0, sizeof ( $logs ) );
		$this->_initRequest ( '/TestCrudController', 'GET' );
		Startup::run ( $this->config );
		$logs = $this->logger->asObjects ( false, null, LoggerParams::DATABASE );
		$this->assertEquals ( 6, sizeof ( $logs ) );
		$this->assertInstanceOf ( LogMessage::class, $logs [0] );
		$log = $logs [0];
		$this->assertEquals ( "info", $log->getLevel () );
		$this->assertEquals ( LoggerParams::DATABASE, $log->getContext () );
	}

	/**
	 * Tests Logger::critical()
	 */
	public function testForceLogs(){
		$logs = $this->logger->asObjects ();
		$this->assertEquals ( 0, count ( $logs ) );
		$this->_initRequest ( '/TestController/logs', 'GET' );
		Startup::run ( $this->config );
		$logs = $this->logger->asObjects ( false, null, 'logs' );
		$this->assertEquals ( 3, sizeof ( $logs ) );
		$log = $logs [0];
		$this->assertEquals ( "critical", $log->getLevel () );
		$this->assertEquals ( 'logs', $log->getContext () );
		$this->assertInstanceOf(\DateTimeImmutable::class, $log->getDatetime());
		$this->assertEquals(15, $log->getExtra()->id);
	}
}

