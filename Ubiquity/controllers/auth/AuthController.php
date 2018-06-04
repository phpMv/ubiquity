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
use Ubiquity\utils\http\UCookie;

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
	protected $_attemptsSessionKey="_attempts";
	
	public function __construct(){
		parent::__construct();
		$this->_controller=Startup::getController();
		$this->_action=Startup::getAction();
		$this->_actionParams=Startup::getActionParams();
		$this->_noAccessMsg=new FlashMessage("You are not authorized to access the page <b>{url}</b> !","Forbidden access","error","warning circle");
		$this->_loginCaption="Log in";
	}
	
	public function index(){
		if(($nbAttempsMax=$this->attemptsNumber())!=null){
			$nb=USession::getTmp($this->_attemptsSessionKey,$nbAttempsMax);
			if($nb<=0){
				$this->badLogin();
				return;
			}
		}
		$this->authLoadView($this->_getFiles()->getViewIndex(),["action"=>$this->_getBaseRoute()."/connect",
				"loginInputName"=>$this->_getLoginInputName(),"loginLabel"=>$this->loginLabel(),
				"passwordInputName"=>$this->_getPasswordInputName(),"passwordLabel"=>$this->passwordLabel(),
				"rememberCaption"=>$this->rememberCaption()
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
		$fMessage=$this->_noAccessMsg;
		$this->noAccessMessage($fMessage);
		$message=$this->fMessage($fMessage->parseContent(["url"=>implode("/",$urlParts)]));		
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute(),"bodySelector"=>$this->_getBodySelector(),"_loginCaption"=>$this->_loginCaption]);
	}
	
	/**
	 * Override for modifying the noAccess message
	 * @param FlashMessage $fMessage
	 */
	protected function noAccessMessage(FlashMessage $fMessage){
		
	}
	
	/**
	 * Override for modifying attempts message
	 * You can use {_timer} and {_attemptsCount} variables in message content
	 * @param FlashMessage $fMessage
	 * @param int $attempsCount
	 */
	protected function attemptsNumberMessage(FlashMessage $fMessage,$attempsCount){
		
	}
	
	/**
	 * Override to implement the complete connection procedure 
	 */
	public function connect(){
		if(URequest::isPost()){
			if($connected=$this->_connect()){
				if(isset($_POST["ck-remember"])){
					$this->rememberMe($connected);
				}
				if(USession::exists($this->_attemptsSessionKey)){
					USession::delete($this->_attemptsSessionKey);
				}
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
		$attemptsMessage="";
		if(($nbAttempsMax=$this->attemptsNumber())!=null){
			$nb=USession::getTmp($this->_attemptsSessionKey,$nbAttempsMax);
			$nb--;
			if($nb<0) $nb=0;
			if($nb==0){
				$fAttemptsNumberMessage=$this->noAttempts();
			}else{
				$fAttemptsNumberMessage=new FlashMessage("<i class='ui warning icon'></i> You still have {_attemptsCount} attempts to log in.",null,"bottom attached warning","");
			}
			USession::setTmp($this->_attemptsSessionKey, $nb,$this->attemptsTimeout());
			$this->attemptsNumberMessage($fAttemptsNumberMessage,$nb);
			$fAttemptsNumberMessage->parseContent(["_attemptsCount"=>$nb,"_timer"=>"<span id='timer'></span>"]);
			$attemptsMessage=$this->fMessage($fAttemptsNumberMessage,"timeout-message");
			$fMessage->addType("attached");
		}
		$message=$this->fMessage($fMessage,"bad-login").$attemptsMessage;
		$this->authLoadView($this->_getFiles()->getViewNoAccess(),["_message"=>$message,"authURL"=>$this->_getBaseRoute(),"bodySelector"=>$this->_getBodySelector(),"_loginCaption"=>$this->_loginCaption]);
	}
	
	protected function noAttempts(){
		$timeout=$this->attemptsTimeout();
		$plus="";
		if(is_numeric($timeout)){
			$this->jquery->exec("$('._login').addClass('disabled');",true);
			$plus=" You can try again {_timer}";
			$this->jquery->exec("var startTimer=function(duration, display) {var timer = duration, minutes, seconds;
    										var interval=setInterval(function () {
        										minutes = parseInt(timer / 60, 10);seconds = parseInt(timer % 60, 10);
										        minutes = minutes < 10 ? '0' + minutes : minutes;
        										seconds = seconds < 10 ? '0' + seconds : seconds;
										        display.html(minutes + ':' + seconds);
										        if (--timer < 0) {clearInterval(interval);$('#timeout-message').hide();$('#bad-login').removeClass('attached');$('._login').removeClass('disabled');}
    										}, 1000);
										}",true);
			$timeToLeft=USession::getTimeout($this->_attemptsSessionKey);
			$this->jquery->exec("startTimer({$timeToLeft},$('#timer'));",true);
			$this->jquery->compile($this->view);
		}
		return new FlashMessage("<i class='ui warning icon'></i> You have no more attempt of connection !".$plus,null,"bottom attached error","");
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
	
	protected function fMessage(FlashMessage $fMessage,$id=null){
		return $this->message($fMessage->getType(), $fMessage->getTitle(), $fMessage->getContent(),$fMessage->getIcon(),$id);
	}
	
	public function message($type,$header,$body,$icon="info",$id=null){
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
	 * To override for getting active user, default : USession::get("activeUser")
	 * @return string
	 */
	public function _getActiveUser(){
		return USession::get($this->_getUserSessionKey());
	}
	
	/**
	 * To override
	 * Returns the maximum number of allowed login attempts
	 */
	protected function attemptsNumber(){
		return;
	}
	
	/**
	 * To override
	 * Returns the time before trying to connect again
	 * Effective only if attemptsNumber return a number
	 * @return number
	 */
	protected function attemptsTimeout(){
		return 3*60;
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
	
	protected function rememberCaption(){
		return "Remember me";
	}
	
	protected function getViewVars($viewname){
		return ["authURL"=>$this->_getBaseRoute(),"bodySelector"=>$this->_getBodySelector(),"_loginCaption"=>$this->_loginCaption];
	}
	
	/**
	 * Saves the connected user identifier in a cookie
	 * @param object $connected
	 */
	protected function rememberMe($connected){
		$id= $this->toCookie($connected);
		if(isset($id)){
			UCookie::set($this->_getUserSessionKey(),$id);
		}
	}
	
	/**
	 * Returns the cookie for auto connection
	 * @return NULL|string
	 */
	protected function getCookieUser(){
		return UCookie::get($this->_getUserSessionKey());
	}
	
	/**
	 * Returns the value from connected user to save it in the cookie for auto connection
	 * @param object $connected
	 */
	protected function toCookie($connected){
		return;
	}
	
	/**
	 * Loads the user from database using the cookie value
	 * @param string $cookie
	 */
	protected function fromCookie($cookie){
		return;
	}
	
	/**
	 * Auto connect the user
	 */
	public function _autoConnect() {
		$cookie=$this->getCookieUser();
		if(isset($cookie)){
			$user=$this->fromCookie($cookie);
			if(isset($user)){
				USession::set($this->_getUserSessionKey(), $user);
			}
		}
	}
	/**
	 * Deletes the cookie for auto connection and returns to index
	 */
	public function forgetConnection(){
		UCookie::delete($this->_getUserSessionKey());
		$this->index();
	}
}
