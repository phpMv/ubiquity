<?php
namespace Ubiquity\controllers\auth;

use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\flash\FlashMessage;
use Ubiquity\controllers\ControllerBase;
use Ubiquity\controllers\Auth\AuthFiles;
use Ubiquity\cache\ClassUtils;

 /**
 * Controller Auth
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 **/
abstract class AuthController extends ControllerBase{
	/**
	 * @var AuthFiles
	 */
	protected $authFiles;
	
	public function index(){
		$this->authLoadView($this->_getFiles()->getViewIndex(),["action"=>$this->_getBaseRoute()."/connect"]);
	}
	
	/**
	 * To override
	 * Return the base route for this Auth controller
	 * @return string
	 */
	public function _getBaseRoute(){
		return ClassUtils::getClassSimpleName(get_class($this));
	}
	/**
	 * {@inheritDoc}
	 * @see \controllers\ControllerBase::isValid()
	 */
	public final function isValid() {
		return true;
	}
	
	/**
	 * Action called when the user does not have access rights to a requested resource
	 * @param array $urlParts
	 */
	public function noAccess($urlParts){
		USession::set("urlParts", $urlParts);
		$fMessage=new FlashMessage("You are not authorized to access the page <b>".implode("/",$urlParts)."</b> !","Forbidden access","error","warning circle");
		$this->noAccessMessage($fMessage);
		$message=$this->fMessage($fMessage);		
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute()]);
	}
	
	/**
	 * Override for modifying the noAccess message
	 * @param FlashMessage $fMessage
	 */
	protected function noAccessMessage(FlashMessage $fMessage){
		
	}
	
	/**
	 * Override to implement the complete connection procedure 
	 */
	public function connect(){
		if(URequest::isPost()){
			if($connected=$this->_connect()){
				$this->onConnect($connected);
			}else{
				$this->onBadCreditentials();
			}
		}
	}
	
	/**
	 * Processes the data posted by the login form
	 * Have to return the connected user instance
	 */
	abstract protected function _connect();
	
	/**
	 * @param object $connected
	 */
	abstract protected function onConnect($connected);
	
	/**
	 * To override for defining a new action when creditentials are invalid
	 */
	protected function onBadCreditentials(){
		$this->badLogin();
	}
	
	/**
	 * Default Action for invalid creditentials
	 */
	public function badLogin(){
		$fMessage=new FlashMessage("Invalid creditentials!","Connection problem","warning","warning circle");
		$this->badLoginMessage($fMessage);
		$message=$this->fMessage($fMessage);
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute()]);
	}
	
	/**
	 * To override for modifying the bad login message
	 * @param FlashMessage $fMessage
	 */
	protected function badLoginMessage(FlashMessage $fMessage){
		
	}
	
	private function authLoadView($viewName,$vars=[]){
		$files=$this->_getFiles();
		$mainTemplate=$files->getBaseTemplate();
		if(isset($mainTemplate)){
			$vars["_viewname"]=$viewName;
			$vars["_base"]=$mainTemplate;
			$this->loadView($files->getViewBaseTemplate(),$vars);
		}else{
			$this->loadView($viewName,$vars);
		}
	}
	
	/**
	 * Logout action
	 * Terminate the session and display a logout message
	 */
	public function terminate(){
		USession::terminate();
		$fMessage=new FlashMessage("You have been properly disconnected!","Logout","success","checkmark");
		$this->terminateMessage($fMessage);
		$message=$this->fMessage($fMessage);
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute()]);
	}
	
	/**
	 * To override for modifying the logout message
	 * @param FlashMessage $fMessage
	 */
	protected function terminateMessage(FlashMessage $fMessage){
		
	}
	
	/**
	 * Action displaying the logged user information 
	 */
	public function info(){
		$this->authLoadView($this->_getFiles()->getViewInfo(),["connected"=>USession::get($this->_getUserSessionKey()),"authURL"=>$this->_getBaseRoute()]);
	}
	
	protected function fMessage(FlashMessage $fMessage){
		return $this->message($fMessage->getType(), $fMessage->getTitle(), $fMessage->getContent(),$fMessage->getIcon());
	}
	
	public function message($type,$header,$body,$icon="info"){
		return $this->loadView($this->_getFiles()->getViewMessage(),get_defined_vars(),true);
	}
	
	protected function getOriginalURL(){
		return USession::get("urlParts");
	}
	
	/**
	 * To override for defining user session key, default : "activeUser"
	 * @return string
	 */
	public function _getUserSessionKey(){
		return "activeUser";
	}
	
	/**
	 * 
	 */
	abstract public function _isValidUser();
	
	/**
	 * To override for changing view files
	 * @return AuthFiles
	 */
	protected function getFiles ():AuthFiles{
		return new AuthFiles();
	}
	
	private function _getFiles():AuthFiles{
		if(!isset($this->authFiles)){
			$this->authFiles=$this->getFiles();
		}
		return $this->authFiles;
	}

}
