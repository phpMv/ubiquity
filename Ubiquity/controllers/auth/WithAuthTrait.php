<?php
namespace Ubiquity\controllers\auth;

use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\Startup;

/**
 * ControllerBase
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 **/
trait WithAuthTrait{
	protected $_checkConnectionContent;
	
	/**
	 * @var AuthController
	 */
	protected $authController;
	
	public function initialize(){
		parent::initialize();
		$authController=$this->_getAuthController();
		if(!URequest::isAjax()){
			if(!$authController->_displayInfoAsString()){
				$authController->info();
			}
			if($this->isValid(Startup::getAction())){
				$this->_checkConnectionContent=$this->checkConnection($authController);
			}else{
				if($authController->_checkConnectionTimeout()!==null)
					$this->jquery->clearInterval("_checkConnection");
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\Controller::loadView()
	 */
	public function loadView($viewName, $pData = NULL, $asString = false) {
		if(!URequest::isAjax() && $this->_getAuthController()->_displayInfoAsString()){
			$this->view->setVar("_userInfo",$this->_getAuthController()->info());
		}
		return parent::loadView ($viewName,$pData,$asString);
	}

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\Controller::isValid()
	 */
	public function isValid($action) {
		$authCtrl=$this->_getAuthController();
		$isValid=$authCtrl->_isValidUser($action);
		if(!$isValid){
			$authCtrl->_autoConnect();
			return $authCtrl->_isValidUser($action);
		}
		return $isValid;
	}

	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\Controller::onInvalidControl()
	 */
	public function onInvalidControl() {
		$auth=$this->_getAuthController();
		if(URequest::isAjax()){
			$this->jquery->get($auth->_getBaseRoute()."/noAccess/".implode(".", Startup::$urlParts),$auth->_getBodySelector(),["historize"=>false]);	
			echo $this->jquery->compile($this->view);
		}else{
			parent::initialize();
			$auth->noAccess(Startup::$urlParts);
			parent::finalize();
		}
		exit();
	}
	
	/**
	 * @return \Ubiquity\controllers\auth\AuthController
	 */
	protected function _getAuthController():AuthController{
		if(!isset($this->authController)){
			$this->authController=$this->getAuthController();
			Startup::injectDependences($this->authController);
		}
		return $this->authController;
	}
	
	protected abstract function getAuthController():AuthController;
	
	
	protected function checkConnection($authController){
		if($authController->_checkConnectionTimeout()!==null){
			$ret=$authController->_disconnected();
			$this->jquery->ajaxInterval("get",$authController->_getBaseRoute()."/checkConnection/",$authController->_checkConnectionTimeout(),"_checkConnection","",["historize"=>false,"jsCallback"=>"data=($.isPlainObject(data))?data:JSON.parse(data);if(!data.valid){ $('#disconnected-modal').modal({closable: false}).modal('show');clearInterval(window._checkConnection);}"]);
			return $ret;
		}	
	}
	
	public function finalize(){
		parent::finalize();
		if(isset($this->_checkConnectionContent)){
			echo $this->_checkConnectionContent;
		}
	}

}
