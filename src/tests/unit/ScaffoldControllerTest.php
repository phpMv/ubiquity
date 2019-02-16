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

	/**
	 * Tests AdminScaffoldController::addCrudController()
	 */
	public function testAddCrudController() {
		$this->scaffoldController->addCrudController ( "TestScaffoldCrudUser", User::class );
		$this->assertTrue ( class_exists ( "controllers\\TestScaffoldCrudUser" ) );
	}
}

