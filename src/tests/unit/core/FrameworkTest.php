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

	/**
	 * Tests Framework::getVersion()
	 */
	public function testGetVersion() {
		Framework::getVersion(/* parameters */);
	}

	/**
	 * Tests Framework::getController()
	 */
	public function testGetController() {
		Framework::getController(/* parameters */);
	}

	/**
	 * Tests Framework::getAction()
	 */
	public function testGetAction() {
		// TODO Auto-generated FrameworkTest::testGetAction()
		$this->markTestIncomplete ( "getAction test not implemented" );

		Framework::getAction(/* parameters */);
	}

	/**
	 * Tests Framework::getUrl()
	 */
	public function testGetUrl() {
		// TODO Auto-generated FrameworkTest::testGetUrl()
		$this->markTestIncomplete ( "getUrl test not implemented" );

		Framework::getUrl(/* parameters */);
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
		// TODO Auto-generated FrameworkTest::testDiSemantic()
		$this->markTestIncomplete ( "diSemantic test not implemented" );

		Framework::diSemantic(/* parameters */);
	}

	/**
	 * Tests Framework::diBootstrap()
	 */
	public function testDiBootstrap() {
		// TODO Auto-generated FrameworkTest::testDiBootstrap()
		$this->markTestIncomplete ( "diBootstrap test not implemented" );

		Framework::diBootstrap(/* parameters */);
	}
}

