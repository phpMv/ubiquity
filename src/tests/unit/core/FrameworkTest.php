<?php
use Ubiquity\core\Framework;
use Ubiquity\controllers\Router;
use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\UCookie;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\contents\normalizers\NormalizersManager;
use Ubiquity\assets\AssetsManager;
use Ubiquity\controllers\Startup;
use Ajax\JsUtils;
use Ajax\Semantic;
use controllers\TestController;
use Ajax\Bootstrap;

/**
 * Framework test case.
 */
class FrameworkTest extends BaseTest {

	/**
	 *
	 * @var Framework
	 */
	private $framework;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->framework = new Framework ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->framework = null;
		parent::_after ();
	}

	protected function _startServices($what = false) {
		$this->_startCache ();
		$this->_startRouter ( $what );
	}

	/**
	 * Tests Framework::getVersion()
	 */
	public function testGetVersion() {
		$version = Framework::getVersion ();
		$this->assertEquals ( Framework::version, $version );
	}

	/**
	 * Tests Framework::getController()
	 */
	public function testGetController() {
		$this->_startServices ();
		$this->_initRequest ( 'TestController', 'GET' );
		Startup::run ( $this->config );
		$ctrl = Framework::getController ();
		$this->assertEquals ( 'controllers\TestController', $ctrl );
		$this->assertEquals ( 'index', Framework::getAction () );
	}

	/**
	 * Tests Framework::getAction()
	 */
	public function testGetAction() {
		$this->_startServices ();
		$this->_initRequest ( 'TestController/test', 'GET' );
		Startup::run ( $this->config );
		$action = Framework::getAction ();
		$this->assertEquals ( 'test', $action );
	}

	/**
	 * Tests Framework::getUrl()
	 */
	public function testGetUrl() {
		$this->_startServices ();
		$this->_initRequest ( 'TestController', 'GET' );
		Startup::run ( $this->config );
		$url = Framework::getUrl ();
		$this->assertEquals ( 'TestController', $url );
	}

	/**
	 * Tests Framework::getRouter()
	 */
	public function testGetRouter() {
		$router = Framework::getRouter ();
		$this->assertInstanceOf ( Router::class, $router );
	}

	/**
	 * Tests Framework::getORM()
	 */
	public function testGetORM() {
		$orm = Framework::getORM ();
		$this->assertInstanceOf ( OrmUtils::class, $orm );
	}

	/**
	 * Tests Framework::getRequest()
	 */
	public function testGetRequest() {
		$uRequest = Framework::getRequest ();
		$this->assertInstanceOf ( URequest::class, $uRequest );
	}

	/**
	 * Tests Framework::getSession()
	 */
	public function testGetSession() {
		$session = Framework::getSession ();
		$this->assertInstanceOf ( USession::class, $session );
	}

	/**
	 * Tests Framework::getCookies()
	 */
	public function testGetCookies() {
		$uCookies = Framework::getCookies ();
		$this->assertInstanceOf ( UCookie::class, $uCookies );
	}

	/**
	 * Tests Framework::getTranslator()
	 */
	public function testGetTranslator() {
		$trans = Framework::getTranslator ();
		$this->assertInstanceOf ( TranslatorManager::class, $trans );
	}

	/**
	 * Tests Framework::getNormalizer()
	 */
	public function testGetNormalizer() {
		$norm = Framework::getNormalizer ();
		$this->assertInstanceOf ( NormalizersManager::class, $norm );
	}

	/**
	 * Tests Framework::hasAdmin()
	 */
	public function testHasAdmin() {
		$this->assertTrue ( Framework::hasAdmin () );
	}

	/**
	 * Tests Framework::getAssets()
	 */
	public function testGetAssets() {
		$assets = Framework::getAssets ();
		$this->assertInstanceOf ( AssetsManager::class, $assets );
	}

	/**
	 * Tests Framework::diSemantic()
	 */
	public function testDiSemantic() {
		$jquery = Framework::diSemantic ( new TestController () );
		$this->assertInstanceOf ( JsUtils::class, $jquery );
		$this->assertInstanceOf ( Semantic::class, $jquery->semantic () );
	}

	/**
	 * Tests Framework::diBootstrap()
	 */
	public function testDiBootstrap() {
		$jquery = Framework::diBootstrap ( new TestController () );
		$this->assertInstanceOf ( JsUtils::class, $jquery );
		$this->assertInstanceOf ( Bootstrap::class, $jquery->bootstrap () );
	}
}

