<?php

namespace Ubiquity\controllers\auth;

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
 */
trait AuthControllerValidationTrait {
	
	public function bad2FACode(){
		$this->confirm();
		$fMessage = new FlashMessage ( 'Invalid 2FA code!', 'Two Factor Authentification', 'warning', 'warning circle' );
		$this->twoFABadCodeMessage( $fMessage );
		$message = $this->fMessage ( $fMessage, 'bad-code' );
		$this->authLoadView ( $this->_getFiles ()->getViewBadTwoFACode(), [ '_message' => $message,'url' => $this->getBaseUrl ().'/sendNew2FACode','bodySelector' => '#bad-two-fa','_btCaption' => 'Send new code' ] );
	}
	
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
	
	protected function generateEmailValidationUrl($email):string {
		$key=\uniqid('v',true);
		$d=new \DateTime();
		$data=['email'=>$email,'expire'=>$d->add($this->emailValidationDuration())];
		CacheManager::$cache->store('auth/'.$key, $data);
		return $key.'/'.\md5($email);
	}
	
	protected function prepareEmailValidation(string $email){
		$validationURL=$this->getBaseUrl().'/checkEmail/'.$this->generateEmailValidationUrl($email);
		$this->_sendEmailValidation($email, $validationURL);
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

