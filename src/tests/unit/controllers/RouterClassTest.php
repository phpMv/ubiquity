<?php
use Ubiquity\controllers\Router;

/**
 * Router test case.
 */
class RouterClassTest extends BaseTest {

	/**
	 *
	 * @var Router
	 */
	private $router;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->_startServices ();
		$this->router = new Router ();
		$this->router->startAll ();
	}

	protected function _startServices($what = false) {
		$this->_startCache ();
		$this->_startRouter ( $what );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->router = null;
	}

	/**
	 * Tests Router::start()
	 */
	public function testStart() {
		$this->router->start ();
	}

	/**
	 * Tests Router::startRest()
	 */
	public function testStartRest() {
		$this->router->startRest ();
	}

	/**
	 * Tests Router::startAll()
	 */
	public function testStartAll() {
		$this->router->startAll ();
	}

	/**
	 * Tests Router::getRoute()
	 */
	public function testGetRoute() {
		$this->assertIsArray ( $this->router->getRoute ( 'route/test/index' ) );
		$this->assertFalse ( $this->router->getRoute ( 'route/test/index__' ) );
	}

	/**
	 * Tests Router::getRouteByName()
	 */
	public function testGetRouteByName() {
		$this->assertEquals ( '/route/test/withView/(.+?)/', $this->router->getRouteByName ( 'withView' ) );
		$this->assertFalse ( $this->router->getRouteByName ( 'withView2' ) );
	}

	/**
	 * Tests Router::getRouteInfoByName()
	 */
	public function testGetRouteInfoByName() {
		$r = Router::getRouteInfoByName ( 'withView' );
		$this->assertEquals ( 'withView', $r ['action'] );
		$this->assertEquals ( 'controllers\\TestController', $r ['controller'] );
		$this->assertFalse ( $this->router->getRouteInfoByName ( 'withView2' ) );
	}

	/**
	 * Tests Router::path()
	 */
	public function testPath() {
		// TODO Auto-generated RouterTest_::testPath()
		$this->markTestIncomplete ( "path test not implemented" );

		Router::path(/* parameters */);
	}

	/**
	 * Tests Router::url()
	 */
	public function testUrl() {
		// TODO Auto-generated RouterTest_::testUrl()
		$this->markTestIncomplete ( "url test not implemented" );

		Router::url(/* parameters */);
	}

	/**
	 * Tests Router::getRouteUrlParts()
	 */
	public function testGetRouteUrlParts() {
		// TODO Auto-generated RouterTest_::testGetRouteUrlParts()
		$this->markTestIncomplete ( "getRouteUrlParts test not implemented" );

		Router::getRouteUrlParts(/* parameters */);
	}

	/**
	 * Tests Router::slashPath()
	 */
	public function testSlashPath() {
		// TODO Auto-generated RouterTest_::testSlashPath()
		$this->markTestIncomplete ( "slashPath test not implemented" );

		Router::slashPath(/* parameters */);
	}

	/**
	 * Tests Router::setExpired()
	 */
	public function testSetExpired() {
		// TODO Auto-generated RouterTest_::testSetExpired()
		$this->markTestIncomplete ( "setExpired test not implemented" );

		Router::setExpired(/* parameters */);
	}

	/**
	 * Tests Router::getRoutes()
	 */
	public function testGetRoutes() {
		$this->assertIsArray ( $this->router->getRoutes () );
	}

	protected function getCacheDirectory() {
		return "cache/";
	}
}

