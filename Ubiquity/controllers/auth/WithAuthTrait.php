<?php
namespace Ubiquity\controllers\auth;

use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\Startup;

/**
 * ControllerBase
 **/
trait WithAuthTrait{
	
	/**
	 * @var AuthController
	 */
	protected $authController;
	
	public function initialize(){
		parent::initialize();
		if(!URequest::isAjax()){
			$this->_getAuthController()->info();
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\Controller::isValid()
	 */
	public function isValid() {
		return $this->_getAuthController()->_isValidUser();
	}

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\Controller::onInvalidControl()
	 */
	public function onInvalidControl() {
		$auth=$this->_getAuthController();
		$auth->initialize();
		$auth->noAccess(Startup::$urlParts);
		$auth->finalize();
		exit();
	}
	
	/**
	 * @return \Ubiquity\controllers\auth\AuthController
	 */
	protected function _getAuthController():AuthController{
		if(!isset($this->authController)){
			$this->authController=$this->getAuthController();
		}
		return $this->authController;
	}
	
	protected abstract function getAuthController():AuthController;

}
