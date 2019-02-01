<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\flash\FlashMessage;

/**
 * @author jcheron <myaddressmail@gmail.com>
 * @property \Ubiquity\controllers\Auth\AuthFiles $authFiles
 * @property string $_loginCaption
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 */
trait AuthControllerCoreTrait {
	abstract public function loadView($viewName, $pData = NULL, $asString = false);
	abstract protected function attemptsTimeout();
	abstract protected function getFiles ():AuthFiles;
	abstract public function _getBodySelector();
	abstract public function _getBaseRoute();
	
	protected  function getBaseUrl(){
		return URequest::getUrl($this->_getBaseRoute());
	}
	
	public function message($type,$header,$body,$icon="info",$id=null){
		return $this->loadView($this->_getFiles()->getViewMessage(),get_defined_vars(),true);
	}
	
	protected function fMessage(FlashMessage $fMessage,$id=null){
		return $this->message($fMessage->getType(), $fMessage->getTitle(), $fMessage->getContent(),$fMessage->getIcon(),$id);
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
	
	
	protected function authLoadView($viewName,$vars=[]){
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
	
	protected function getOriginalURL(){
		return USession::get("urlParts");
	}
	
	protected  function _getFiles():AuthFiles{
		if(!isset($this->authFiles)){
			$this->authFiles=$this->getFiles();
		}
		return $this->authFiles;
	}
	
	protected function getViewVars($viewname){
		return ["authURL"=>$this->getBaseUrl(),"bodySelector"=>$this->_getBodySelector(),"_loginCaption"=>$this->_loginCaption];
	}
}

