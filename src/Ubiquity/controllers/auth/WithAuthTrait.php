<?php
namespace Ubiquity\controllers\auth;

use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\Startup;

/**
 * 
 * For adding authentification on a controller.
 * 
 * Ubiquity\controllers\auth$WithAuthTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 * 
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 *
 */
trait WithAuthTrait {

	protected $_checkConnectionContent;

	/**
	 *
	 * @var AuthController
	 */
	protected $authController;

	public function initialize() {
		$authController = $this->_getAuthController();
		$authController->_init();
		parent::initialize();
		if (! URequest::isAjax() || URequest::has('_userInfo')) {
			if (! $authController->_displayInfoAsString()) {
				$authController->info();
			}
			if ($this->isValid(Startup::getAction())) {
				$this->_checkConnectionContent = $this->checkConnection($authController);
			} else {
				if ($authController->_checkConnectionTimeout() !== null)
					$this->jquery->clearInterval('_checkConnection');
			}
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\Controller::loadView()
	 */
	public function loadView(string $viewName, $pData = NULL, bool $asString = false) {
		if ((! URequest::isAjax() && $this->_getAuthController()->_displayInfoAsString()) || URequest::has('_userInfo')) {
			$this->view->setVar('_userInfo', $this->_getAuthController()
				->info());
		}
		return parent::loadView($viewName, $pData, $asString);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\Controller::isValid()
	 */
	public function isValid($action) {
		$authCtrl = $this->_getAuthController();
		$isValid = $authCtrl->_isValidUser($action);
		if (! $isValid) {
			$authCtrl->_autoConnect();
			return $authCtrl->_isValidUser($action);
		}
		return $isValid;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\Controller::onInvalidControl()
	 */
	public function onInvalidControl() {
		$auth = $this->_getAuthController();
		if (URequest::isAjax()) {
			$this->jquery->get($auth->_getBaseRoute() . '/noAccess/' . \implode('.', Startup::$urlParts), $auth->_getBodySelector(), [
				'historize' => false
			]);
			echo $this->jquery->compile($this->view);
		} else {
			$this->initialize();
			$auth->noAccess(Startup::$urlParts);
			$this->finalize();
		}
		exit();
	}

	/**
	 *
	 * @return \Ubiquity\controllers\auth\AuthController
	 */
	protected function _getAuthController(): AuthController {
		if (! isset($this->authController)) {
			$this->authController = $this->getAuthController();
			Startup::injectDependencies($this->authController);
		}
		return $this->authController;
	}

	protected abstract function getAuthController(): AuthController;

	protected function checkConnection($authController) {
		if ($authController->_checkConnectionTimeout() !== null) {
			$ret = $authController->_disconnected();
			$this->jquery->ajaxInterval("get", $authController->_getBaseRoute() . '/checkConnection/', $authController->_checkConnectionTimeout(), '_checkConnection', '', [
				'historize' => false,
				'jsCallback' => "data=($.isPlainObject(data))?data:JSON.parse(data);if(!data.valid){ $('#disconnected-modal').modal({closable: false}).modal('show');clearInterval(window._checkConnection);}"
			]);
			return $ret;
		}
	}

	public function finalize() {
		parent::finalize();
		if (isset($this->_checkConnectionContent)) {
			echo $this->_checkConnectionContent;
		}
	}
}
