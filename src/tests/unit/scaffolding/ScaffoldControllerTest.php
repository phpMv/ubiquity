<?php
use Ubiquity\scaffolding\AdminScaffoldController;
use controllers\Admin;
use Ubiquity\controllers\Startup;
use models\User;

/**
 * ScaffoldController test case.
 */
class ScaffoldControllerTest extends BaseTest {

	/**
	 *
	 * @var AdminScaffoldController
	 */
	private $scaffoldController;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->_startServices ();
		Startup::setConfig ( $this->config );
		$adminController = new Admin ();
		Startup::injectDependencies ( $adminController );
		$this->scaffoldController = new AdminScaffoldController ( $adminController, $adminController->jquery );
	}

	protected function _startServices($what = false) {
		$this->_startCache ();
		$this->_startRouter ( $what );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->scaffoldController = null;

		parent::_after ();
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
		$this->assertStringContainsString ( $result, $res );
	}

	/**
	 * Tests AdminScaffoldController::addCrudController()
	 */
	public function testAddCrudController() {
		$this->scaffoldController->addCrudController ( "TestScaffoldCrudUser", User::class, true, true, true, "index,form,display", "scaff/test" );
		$this->assertTrue ( class_exists ( "controllers\\TestScaffoldCrudUser" ) );

		$this->_initRequest ( 'TestScaffoldCrudUser', 'GET' );
		$this->_assertDisplayContains ( function () {
			Startup::run ( $this->config );
			$this->assertEquals ( "controllers\\TestScaffoldCrudUser", Startup::getController () );
			$this->assertEquals ( 'index', Startup::getAction () );
		}, 'benjamin.sherman@gmail.com' );

		$this->_initRequest ( 'TestScaffoldCrudUser/edit/modal/2', 'GET' );
		$this->_assertDisplayContains ( function () {
			Startup::run ( $this->config );
			$this->assertEquals ( "controllers\\TestScaffoldCrudUser", Startup::getController () );
		}, 'acton.carrillo@gmail.com' );

		$this->_initRequest ( 'TestScaffoldCrudUser/delete/3', 'GET' );
		$_SERVER ['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		$this->_assertDisplayContains ( function () {
			Startup::run ( $this->config );
			$this->assertEquals ( "controllers\\TestScaffoldCrudUser", Startup::getController () );
		}, 'Do you confirm the deletion of' );
	}

	/**
	 * Tests AdminScaffoldController::addController()
	 */
	public function testAddControllerAndAction() {
		$this->scaffoldController->_createController ( "TestNewController", [ "%baseClass%" => "ControllerBase" ] );
		ob_start ();
		$this->scaffoldController->_newAction ( "controllers\\TestNewController", "newAction", "a,b=5", "echo 'test-'.\$a.'-'.\$b;", [ "path" => "/test/new/{a}/{b}/","methods" => "" ], true );
		$res = ob_get_clean ();
		$this->assertStringContainsString ( "The action <b>newAction</b> is created in controller <b>controllers\TestNewController</b>", $res );
		$this->assertStringContainsString ( "Created route : <b>/test/new/{a}/{b}/</b>", $res );
		$this->assertStringContainsString ( "You need to re-init Router cache to apply this update", $res );
		$this->assertStringContainsString ( "Created view : <b>TestNewController/newAction.html</b>", $res );
	}

	/**
	 * Tests AdminScaffoldController::addAuthController()
	 */
	public function testAddAuthController() {
		$this->scaffoldController->addAuthController ( "TestScaffoldAuth", "\\Ubiquity\\controllers\\auth\\AuthController", "index,info,noAccess,disconnected,message,baseTemplate", "crud/test" );
		$this->assertTrue ( class_exists ( "controllers\\TestScaffoldAuth" ) );
	}

	/**
	 * Tests AdminScaffoldController::addAuthControllerConfig()
	 */
	public function testAddAuthControllerConfig() {
		$this->scaffoldController->addAuthController ( "TestScaffoldAuthConfig", "\\Ubiquity\\controllers\\auth\\AuthControllerConfig", "index,info,noAccess,disconnected,message,baseTemplate,initRecovery,recovery", "crud/config" );
		$this->assertTrue ( \class_exists ( "controllers\\TestScaffoldAuthConfig" ) );
		$this->assertTrue ( \class_exists ( "controllers\\auth\\files\\TestScaffoldAuthConfigFiles" ) );
		$this->assertTrue(\file_exists(\ROOT.'/config/testScaffoldAuthConfig.config.php'));
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see BaseTest::getDi()
	 */
	protected function getDi() {
		return [ 'jquery' => function ($controller) {
			$jquery = new \Ajax\php\ubiquity\JsUtils ( [ "defer" => true ], $controller );
			$jquery->semantic ( new \Ajax\Semantic () );
			return $jquery;
		} ];
	}
}

