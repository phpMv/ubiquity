<?php
namespace Ubiquity\controllers;

use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\flash\FlashMessage;

 /**
 * Controller Auth
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 **/
abstract class AuthController extends ControllerBase{

	public function index(){
		$this->jquery->renderView("@framework/auth/index.html");
	}
	/**
	 * {@inheritDoc}
	 * @see \controllers\ControllerBase::isValid()
	 */
	public function isValid() {
		return true;
	}
	
	public function noAccess($urlParts){
		USession::set("urlParts", $urlParts);
		$fMessage=new FlashMessage("You are not authorized to access the page <b>".implode("/",$urlParts)."</b> !","Forbidden access","error","warning circle");
		$this->noAccessMessage($fMessage);
		$message=$this->fMessage($fMessage);		
		$this->loadView("@framework/auth/noAccess.html",["message"=>$message]);
	}
	protected function noAccessMessage(FlashMessage $fMessage){
		
	}
	
	public function connect(){
		if(URequest::isPost()){
			if($connected=$this->_connect()){
				$this->onConnect($connected);
			}else{
				$this->onBadCreditentials();
			}
		}
	}
	
	abstract protected function _connect();
	
	abstract protected function onConnect($connected);
	
	protected function onBadCreditentials(){
		$this->badLogin();
	}
	
	public function badLogin(){
		$fMessage=new FlashMessage("Invalid creditentials!","Connection problem","warning","warning circle");
		$this->badLoginMessage($fMessage);
		$message=$this->fMessage($fMessage);
		$this->loadView("@framework/auth/noAccess.html",["message"=>$message]);
	}
	
	protected function badLoginMessage(FlashMessage $fMessage){
		
	}
	
	public function terminate(){
		USession::terminate();
		$fMessage=new FlashMessage("You have been properly disconnected!","Logout","success","checkmark");
		$this->terminateMessage($fMessage);
		$message=$this->fMessage($fMessage);
		$this->loadView("@framework/auth/noAccess.html",["message"=>$message]);
	}
	
	protected function terminateMessage(FlashMessage $fMessage){
		
	}
	
	public function info($user){
		$this->loadView("@framework/auth/info.html",["connected"=>$user]);
	}
	
	protected function fMessage(FlashMessage $fMessage){
		return $this->message($fMessage->getType(), $fMessage->getTitle(), $fMessage->getContent(),$fMessage->getIcon());
	}
	
	public function message($type,$header,$body,$icon="info"){
		return $this->loadView("@framework/main/message.html",get_defined_vars(),true);
	}
	
	protected function getOriginalURL(){
		return USession::get("urlParts");
	}

}
