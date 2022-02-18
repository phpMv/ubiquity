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

	private static $TWO_FA_KEY='2FA-infos';

	protected static $TOKENS_VALIDATE_EMAIL='email.validation';
	
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

	abstract protected function twoFACodeDuration():\DateInterval;

	

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
	
	protected function save2FACode():array{
		$code=$this->generate2FACode();
		$expire=(new \DateTime())->add($this->twoFACodeDuration());
		$codeInfos=USession::get(self::$TWO_FA_KEY,compact('code','expire'));
		USession::set(self::$TWO_FA_KEY,$codeInfos);
		return $codeInfos;
	}
	
	/**
	 * Submits the 2FA code in post request.
	 * 
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function submitCode(){
		if(URequest::isPost() && USession::exists(self::$TWO_FA_KEY)){
			$twoFAInfos=USession::get(self::$TWO_FA_KEY);
			$expired=$twoFAInfos['expire']<new \DateTime();
			if(!$expired && $this->check2FACode($twoFAInfos['code'],URequest::post('code'))){
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
		$codeInfos=$this->save2FACode();
		$this->_send2FACode($codeInfos['code'], USession::get($this->_getUserSessionKey().'-2FA'));
	}
	
	public function sendNew2FACode(){
		$this->send2FACode();
		$fMessage = new FlashMessage ( 'A new code was submited.', 'Two factor Authentification', 'success', 'key' );
		$this->newTwoFACodeMessage ( $fMessage );
		echo $this->fMessage ( $fMessage );
	}
	
	protected function generateEmailValidationUrl($email):array {
		$duration=$this->emailValidationDuration();
		$tokens=new AuthTokens(self::$TOKENS_VALIDATE_EMAIL,10,$duration->s);
		$d=new \DateTime();
		$dExpire=$d->add($duration);
		$key=$tokens->store(['email'=>$email]);
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
	 * To override for a more secure 2FA code.
	 * @param string $secret
	 * @param string $userInput
	 * @return bool
	 */
	protected function check2FACode(string $secret,string $userInput):bool{
		return $secret===$userInput;
	}
	
	/**
	 * Route for email validation checking.
	 * @param string $key
	 * @param string $hashMail
	 */
	public function checkEmail(string $key,string $hashMail){
		$isValid=false;
		$duration=$this->emailValidationDuration();
		$tokens=new AuthTokens(self::$TOKENS_VALIDATE_EMAIL,10,$duration->s);
		if($tokens->exists($key)){
			if(!$tokens->expired($key)){
				$data=$tokens->fetch($key);
				$email=$data['email'];
				if(\md5($email)===$hashMail && $this->validateEmail($email)){
					$fMessage = new FlashMessage ( "Your email <b>$email</b> has been validated.", 'Account creation', 'success', 'user' );
					$this->emailValidationSuccess($fMessage);
					$isValid=true;
				}
				$msg='This validation link is not valid!';
			}else{
				$msg='This validation link is no longer active!';
			}
		}
		if(!$isValid){
			$fMessage = new FlashMessage ( $msg??'This validation link is not valid!', 'Account creation', 'error', 'user' );
			$this->emailValidationError($fMessage);
		}
		echo $this->fMessage($fMessage);
	}
}

