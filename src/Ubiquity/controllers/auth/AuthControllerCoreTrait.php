<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\flash\FlashMessage;
use Ubiquity\utils\http\UResponse;

/**
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @property AuthFiles $authFiles
 * @property string $_loginCaption
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 */
trait AuthControllerCoreTrait {

	abstract public function loadView($viewName, $pData = NULL, $asString = false);

	abstract protected function attemptsTimeout();

	abstract protected function getFiles(): AuthFiles;

	abstract public function _getBodySelector():string;

	abstract public function _getBaseRoute():string;
	
	abstract protected function _newAccountCreationRule(string $accountName):?bool;
	
	abstract public function _getLoginInputName():string;
	
	abstract protected function hasAccountCreation():bool;

	abstract protected function hasAccountRecovery():bool;

	abstract protected function canCreateAccountMessage(FlashMessage $fMessage);

	abstract protected function getAccountRecoveryLink():string;
	
	protected function getBaseUrl():string {
		return URequest::getUrl ( $this->_getBaseRoute () );
	}
	
	protected function useAjax():bool{
		return true;
	}

	#[\Ubiquity\attributes\items\router\NoRoute()]
	public function message($type, $header, $body, $icon = 'info', $id = null):string {
		return $this->loadView ( $this->_getFiles ()->getViewMessage (), \get_defined_vars (), true );
	}

	protected function fMessage(FlashMessage $fMessage, $id = null):string {
		return $this->message ( $fMessage->getType (), $fMessage->getTitle (), $fMessage->getContent (), $fMessage->getIcon (), $id );
	}
	
	#[\Ubiquity\attributes\items\router\Post]
	public function newAccountCreationRule(){
		if (URequest::isPost()) {
			$result = [];
			UResponse::asJSON();
			$result['result'] = $this->_newAccountCreationRule(URequest::post($this->_getLoginInputName()));
			echo \json_encode($result);
		}
	}

	protected function noAttempts() {
		$timeout = $this->attemptsTimeout ();
		$plus = '';
		if (\is_numeric ( $timeout )) {
			$this->jquery->exec ( "$('._login').addClass('disabled');", true );
			$plus = " You can try again {_timer}";
			$this->jquery->exec ( "var startTimer=function(duration, display) {var timer = duration, minutes, seconds;
											var interval=setInterval(function () {
												minutes = parseInt(timer / 60, 10);seconds = parseInt(timer % 60, 10);
												minutes = minutes < 10 ? '0' + minutes : minutes;
												seconds = seconds < 10 ? '0' + seconds : seconds;
												display.html(minutes + ':' + seconds);
												if (--timer < 0) {clearInterval(interval);$('#timeout-message').hide();$('#bad-login').removeClass('attached');$('._login').removeClass('disabled');}
											}, 1000);
										}", true );
			$timeToLeft = USession::getTimeout ( $this->_attemptsSessionKey );
			$this->jquery->exec ( "startTimer({$timeToLeft},$('#timer'));", true );
			$this->jquery->compile ( $this->view );
		}
		return new FlashMessage ( "<i class='ui warning icon'></i> You have no more attempt of connection !" . $plus, null, "bottom attached error", "" );
	}

	protected function authLoadView($viewName, $vars = [ ]):void {
		if($this->useAjax()){
			$loadView=function($vn,$v){$this->jquery->renderView($vn,$v);};
		}else{
			$loadView=function($vn,$v) {$this->loadView($vn, $v);};
		}
		if($this->hasAccountRecovery()){
			$vars['resetPassword']=$this->getAccountRecoveryLink();
		}
		$files = $this->_getFiles ();
		$mainTemplate = $files->getBaseTemplate ();
		if (isset ( $mainTemplate )) {
			$vars ['_viewname'] = $viewName;
			$vars ['_base'] = $mainTemplate;
			$loadView ( $files->getViewBaseTemplate (), $vars );
		} else {
			$loadView ( $viewName, $vars );
		}
	}

	protected function getOriginalURL() {
		return USession::get ( 'urlParts' );
	}

	protected function _getFiles(): AuthFiles {
		if (! isset ( $this->authFiles )) {
			$this->authFiles = $this->getFiles ();
		}
		return $this->authFiles;
	}

	protected function getViewVars($viewname) {
		return [ 'authURL' => $this->getBaseUrl (),'bodySelector' => $this->_getBodySelector (),'_loginCaption' => $this->_loginCaption ];
	}
	
	protected function addAccountCreationViewData(array &$vData,$forMessage=false){
		if($this->hasAccountCreation()){
			if($forMessage){
				$fMessage = new FlashMessage ( "<p>You can create one now!</p><a href='{createAccountUrl}' class='ui button black ajax _create' data-target='{accountCreationTarget}'>Create account</a>", "Don't have an account yet?", "", "question" );
				$this->canCreateAccountMessage ( $fMessage->parseContent(['accountCreationTarget'=>$this->_getBodySelector(),'createAccountUrl'=>$this->getBaseUrl().'/addAccount']) );
				$vData['canCreateAccountMessage'] = $this->fMessage ( $fMessage );
			}else{
				$vData['createAccountUrl']=$this->getBaseUrl().'/addAccount';
				$vData['accountCreationTarget']=$this->_getBodySelector();
			}
		}
	}
}

