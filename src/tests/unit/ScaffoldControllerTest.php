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

