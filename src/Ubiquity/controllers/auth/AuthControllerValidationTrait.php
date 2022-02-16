<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\utils\base\UDateTime;
use Ubiquity\utils\flash\FlashMessage;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;
use Ubiquity\cache\CacheManager;

/**
 * 
 * Ubiquity\controllers\auth$AuthControllerValidationTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 * 
 * @property bool $_invalid
 *
 */
trait AuthControllerValidationTrait {
	
	abstract protected function twoFABadCodeMessage(FlashMessage $fMessage);
	
	abstract protected function fMessage(FlashMessage $fMessage, $id = null):string;
	
	abstract protected function _getFiles(): AuthFiles;
	
	abstract protected function getBaseUrl():string;
	
	abstract protected function authLoadView($viewName, $vars = [ ]):void;
	
	abstract protected function twoFAMessage(FlashMessage $fMessage);
	
	abstract protected function useAjax():bool;
	
	abstract public function _getBodySelector():string;
	
	abstract protected function generate2FACode():string;
	
	abstract public function _getUserSessionKey():string;
	
	abstract protected function onConnect($connected);
	
	abstract protected function initializeAuth();
	
	abstract protected function finalizeAuth();
	
	abstract protected function onBad2FACode():void;
	
	abstract protected function _send2FACode(string $code,$connected):void;
	
	abstract protected function newTwoFACodeMessage(FlashMessage $fMessage);
	
	abstract protected function emailValidationDuration():\DateInterval;
	
	abstract protected function _sendEmailValidation(string $email,string $validationURL,string $expire):void;
	
	abstract protected function emailValidationSuccess(FlashMessage $fMessage);
	
	abstract protected function emailValidationError(FlashMessage $fMessage);
	
	abstract protected function towFACodePrefix():string;

	/**
	 * @noRoute
	 */
	#[\Ubiquity\attributes\items\router\NoRoute]
	public function bad2FACode():void{
		$this->confirm();
		$fMessage = new FlashMessage ( 'Invalid 2FA code!', 'Two Factor Authentification', 'warning', 'warning circle' );
		$this->twoFABadCodeMessage( $fMessage );
		$message = $this->fMessage ( $fMessage, 'bad-code' );
		$this->authLoadView ( $this->_getFiles ()->getViewBadTwoFACode(), [ '_message' => $message,'url' => $this->getBaseUrl ().'/sendNew2FACode','bodySelector' => '#bad-two-fa','_btCaption' => 'Send new code' ] );
	}

	/**
	 * @noRoute
	 */
	#[\Ubiquity\attributes\items\router\NoRoute]
	public function confirm(){
		$fMessage = new FlashMessage( 'Enter the rescue code and validate.', 'Two factor Authentification', 'info', 'key' );
		$this->twoFAMessage ( $fMessage );
		$message = $this->fMessage ( $fMessage );
		if($this->useAjax()){
			$frm=$this->jquery->semantic()->htmlForm('frm-valid-code');
			$frm->addExtraFieldRule('code','empty');
			$frm->setValidationParams(['inline'=>true,'on'=>'blur']);
		}
		$this->authLoadView ( $this->_getFiles ()->getViewStepTwo(), [ '_message' => $message,'submitURL' => $this->getBaseUrl ().'/submitCode','bodySelector' => $this->_getBodySelector(),'prefix'=>$this->towFACodePrefix() ] );
	}
	
	protected function save2FACode(){
		$code=USession::get('2FACode',$this->generate2FACode());
		USession::set('2FACode',$code);
		return $code;
	}
	
	/**
	 * Submits the 2FA code in post request.
	 * 
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function submitCode(){
		if(URequest::isPost()){
			if(USession::get('2FACode')===URequest::post('code')){
				$this->onConnect(USession::get($this->_getUserSessionKey().'-2FA'));
			}
			else{
				$this->_invalid=true;
				$this->initializeAuth();
				$this->onBad2FACode();
				$this->finalizeAuth();
			}
		}
	}

	/**
	 * @noRoute
	 */
	#[\Ubiquity\attributes\items\router\NoRoute]
	public function send2FACode(){
		$code=$this->save2FACode();
		$this->_send2FACode($code, USession::get($this->_getUserSessionKey().'-2FA'));
	}
	
	public function sendNew2FACode(){
		$this->send2FACode();
		$fMessage = new FlashMessage ( 'A new code was submited.', 'Two factor Authentification', 'success', 'key' );
		$this->newTwoFACodeMessage ( $fMessage );
		echo $this->fMessage ( $fMessage );
	}
	
	protected function generateEmailValidationUrl($email):array {
		$key=\uniqid('v',true);
		$d=new \DateTime();
		$dExpire=$d->add($this->emailValidationDuration());
		$data=['email'=>$email,'expire'=>$dExpire];
		CacheManager::$cache->store('auth/'.$key, $data);
		return ['url'=>$key.'/'.\md5($email),'expire'=>$dExpire];
	}
	
	protected function prepareEmailValidation(string $email){
		$data=$this->generateEmailValidationUrl($email);
		$validationURL=$this->getBaseUrl().'/checkEmail/'.$data['url'];
		$this->_sendEmailValidation($email, $validationURL,UDateTime::elapsed($data['expire']));
	}
	
	/**
	 * To override
	 * Checks an email.
	 *
	 * @param string $mail
	 * @return bool
	 */
	protected function validateEmail(string $mail):bool{
		return true;
	}
	
	/**
	 * Route for email validation checking.
	 * @param string $uuid
	 * @param string $hashMail
	 */
	public function checkEmail(string $uuid,string $hashMail){
		$key='auth/'.$uuid;
		$isValid=false;
		if(CacheManager::$cache->exists($key)){
			$data=CacheManager::$cache->fetch($key);
			$email=$data['email'];
			$date=$data['expire'];
			if($date>new \DateTime()){
				if(\md5($email)===$hashMail){
					if($this->validateEmail($email)){
						$fMessage = new FlashMessage ( "Your email <b>$email</b> has been validated.", 'Account creation', 'success', 'user' );
						$this->emailValidationSuccess($fMessage);
						$isValid=true;
					}
				}
				CacheManager::$cache->remove($key);
				$msg='This validation link is not valid!';
			}else{
				$msg='This validation link is no longer active!';
			}
		}
		if(!$isValid){
			$fMessage = new FlashMessage ( $msg??'This validation link is not valid!', 'Account creation', 'error', 'user' );
			$this->emailValidationError($fMessage);
		}
		$this->initializeAuth();
		echo $this->fMessage($fMessage);
		$this->finalizeAuth();
	}
}
