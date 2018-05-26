<?php
namespace Ubiquity\controllers\auth;

use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\flash\FlashMessage;
use Ubiquity\controllers\ControllerBase;
use Ubiquity\controllers\Auth\AuthFiles;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\http\UResponse;
use Ubiquity\utils\base\UString;
use Ubiquity\controllers\Startup;

 /**
 * Controller Auth
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 **/
abstract class AuthController extends ControllerBase{
	/**
	 * @var AuthFiles
	 */
	protected $authFiles;
	protected $_controller;
	protected $_action;
	protected $_actionParams;
	protected $_noAccessMsg;
	protected $_loginCaption;
	
	public function __construct(){
		parent::__construct();
		$this->_controller=Startup::getController();
		$this->_action=Startup::getAction();
		$this->_actionParams=Startup::getActionParams();
		$this->_noAccessMsg=new FlashMessage("You are not authorized to access the page <b>{url}</b> !","Forbidden access","error","warning circle");
		$this->_loginCaption="Log in";
	}
	
	public function index(){
		$this->authLoadView($this->_getFiles()->getViewIndex(),["action"=>$this->_getBaseRoute()."/connect",
				"loginInputName"=>$this->_getLoginInputName(),"loginLabel"=>$this->loginLabel(),
				"passwordInputName"=>$this->_getPasswordInputName(),"passwordLabel"=>$this->passwordLabel()
		]);
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
	public final function isValid($action) {
		return true;
	}
	
	/**
	 * Action called when the user does not have access rights to a requested resource
	 * @param array|string $urlParts
	 */
	public function noAccess($urlParts){
		if(!is_array($urlParts)){
			$urlParts=explode(".", $urlParts);
		}
		USession::set("urlParts", $urlParts);
		$fMessage=$this->_noAccessMsg->parseContent(["url"=>implode("/",$urlParts)]);
		$this->noAccessMessage($fMessage);
		$message=$this->fMessage($fMessage);		
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute(),"bodySelector"=>$this->_getBodySelector(),"_loginCaption"=>$this->_loginCaption]);
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
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute(),"bodySelector"=>$this->_getBodySelector(),"_loginCaption"=>$this->_loginCaption]);
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
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute(),"bodySelector"=>$this->_getBodySelector(),"_loginCaption"=>$this->_loginCaption]);
	}
	
	public function _disConnected(){
		$fMessage=new FlashMessage("You have been disconnected from the application!","Logout","","sign out");
		$this->disconnectedMessage($fMessage);
		$message=$this->fMessage($fMessage);
		$this->jquery->getOnClick("._signin", $this->_getBaseRoute(),$this->_getBodySelector(),["stopPropagation"=>false,"preventDefault"=>false]);
		$this->jquery->execOn("click", "._close", "window.open(window.location,'_self').close();");
		$this->jquery->renderView($this->_getFiles()->getViewDisconnected(),["_title"=>"Session ended","_message"=>$message]);
	}
	
	/**
	 * To override for modifying the logout message
	 * @param FlashMessage $fMessage
	 */
	protected function terminateMessage(FlashMessage $fMessage){
		
	}
	
	/**
	 * To override for modifying the disconnect message
	 * @param FlashMessage $fMessage
	 */
	protected function disconnectedMessage(FlashMessage $fMessage){
		
	}
	
	/**
	 * Action displaying the logged user information 
	 * if _displayInfoAsString returns true, use _infoUser var in views to display user info
	 * @return string|null
	 */
	public function info(){
		return $this->loadView($this->_getFiles()->getViewInfo(),["connected"=>USession::get($this->_getUserSessionKey()),"authURL"=>$this->_getBaseRoute(),"bodySelector"=>$this->_getBodySelector()],$this->_displayInfoAsString());
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
	
	public function _checkConnection(){
		UResponse::asJSON();
		echo "{\"valid\":".UString::getBooleanStr($this->_isValidUser())."}";
	}
	
	/**
	 * return boolean true if activeUser is valid
	 */
	abstract public function _isValidUser();
	
	/**
	 * To override for changing view files
	 * @return AuthFiles
	 */
	protected function getFiles ():AuthFiles{
		return new AuthFiles();
	}
	
	/**
	 * Override to define if info is displayed as string
	 * if set to true, use _infoUser var in views to display user info
	 */
	public function _displayInfoAsString(){
		return false;
	}
	
	public function _checkConnectionTimeout(){
		return;
	}
	
	private function _getFiles():AuthFiles{
		if(!isset($this->authFiles)){
			$this->authFiles=$this->getFiles();
		}
		return $this->authFiles;
	}
	
	public function _getLoginInputName(){
		return "email";
	}

	protected function loginLabel(){
		return ucfirst($this->_getLoginInputName());
	}
	
	public function _getPasswordInputName(){
		return "password";
	}
	
	protected function passwordLabel(){
		return ucfirst($this->_getPasswordInputName());
	}
	
	public function _getBodySelector(){
		return "body";
	}
	
	/**
	 * Sets the default noAccess message
	 * Default : "You are not authorized to access the page <b>{url}</b> !"
	 * @param string $content
	 * @param string $title
	 * @param string $type
	 * @param string $icon
	 */
	public function _setNoAccessMsg($content,$title=NULL,$type=NULL,$icon=null) {
		$this->_noAccessMsg->setValues($content,$title,$type,$icon);
	}
	/**
	 * @param string $_loginCaption
	 */
	public function _setLoginCaption($_loginCaption) {
		$this->_loginCaption = $_loginCaption;
	}


}
