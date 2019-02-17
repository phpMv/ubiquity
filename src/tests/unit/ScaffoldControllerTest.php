<?php
use Ubiquity\scaffolding\AdminScaffoldController;
use controllers\Admin;
use Ubiquity\controllers\Startup;
use models\User;
use Ubiquity\utils\http\URequest;
use Ubiquity\cache\CacheManager;

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
		Startup::injectDependences ( $adminController );
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
		$this->assertContains ( $result, $res );
	}

	/**
	 * Tests AdminScaffoldController::addCrudController()
	 */
	public function testAddCrudController() {
		$this->scaffoldController->addCrudController ( "TestScaffoldCrudUser", User::class );
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
		$this->assertTrue ( class_exists ( "controllers\\TestNewController" ) );
		$this->scaffoldController->_newAction ( "controllers\\TestNewController", "newAction", "a,b=5", "echo 'test-'.\$a.'-'.\$b;", [ "path" => "/test/new/{a}/{b}/","methods" => "" ] );

		$this->_initRequest ( '/TestNewController/newAction/essai/', 'GET' );
		$this->_assertDisplayContains ( function () {
			Startup::run ( $this->config );
			$this->assertEquals ( "controllers\\TestNewController", Startup::getController () );
		}, 'test-essai-5' );

		$this->_initRequest ( '/TestNewController/newAction/autreEssai/12/', 'GET' );
		$this->_assertDisplayContains ( function () {
			Startup::run ( $this->config );
			$this->assertEquals ( "controllers\\TestNewController", Startup::getController () );
		}, 'test-autreEssai-12' );
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

