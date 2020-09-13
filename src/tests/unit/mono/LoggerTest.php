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

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->config ["debug"] = true;
		$this->config["logger"]=new UMonolog('tests', \Monolog\Logger::INFO);
		Logger::init($this->config);
		Logger::clearAll ();
		$this->_startServices ();
	}

	protected function _startServices($what = false) {
		$this->_startCache ();
		$this->_startRouter ( $what );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		Logger::clearAll();
		$this->config ["debug"] = false;
	}

	/**
	 * Tests Logger::critical()
	 */
	public function testForceLogs(){
		$logs = Logger::asObjects ();
		$this->assertEquals ( 0, count ( $logs ) );
		$this->_initRequest ( 'TestController/logs', 'GET' );
		Startup::run ( $this->config );
		Logger::close();
		$logs = Logger::asObjects ( false, null, ['logs'] );
		$this->assertEquals ( 3, sizeof ( $logs ) );
		$log = $logs [0];
		$this->assertEquals ( \Monolog\Logger::CRITICAL, $log->getLevel () );
		$this->assertEquals ( 'logs', $log->getContext () );
		$this->assertNotNull($log->getDatetime());
		$this->assertEquals('id: 15', \current($log->getExtra()));
	}
}

