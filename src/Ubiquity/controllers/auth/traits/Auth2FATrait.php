<?php

namespace Ubiquity\controllers\auth\traits;

use Ubiquity\controllers\auth\AuthFiles;
use Ubiquity\utils\flash\FlashMessage;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;

/**
 * 
 * Ubiquity\controllers\auth\traits$Auth2FATrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 * 
 * @property bool $_invalid
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 *
 */
trait Auth2FATrait {

	private static $TWO_FA_KEY='2FA-infos';


	abstract protected function fMessage(FlashMessage $fMessage, $id = null):string;

	abstract protected function _getFiles(): AuthFiles;

	abstract protected function getBaseUrl():string;

	abstract protected function authLoadView($viewName, $vars = [ ]):void;

	abstract protected function useAjax():bool;

	abstract public function _getBodySelector():string;

	abstract public function _getUserSessionKey():string;

	abstract protected function onConnect($connected);

	abstract protected function initializeAuth();

	abstract protected function finalizeAuth();


	/**
	 * To override
	 * Returns true for a two factor authentification for this account.
	 * @param mixed $accountValue
	 * @return bool
	 */
	protected function has2FA($accountValue=null):bool{
		return false;
	}

	/**
	 * To override for defining a new action when 2FA code is invalid.
	 */
	protected function onBad2FACode():void{
		$this->bad2FACode();
	}

	/**
	 * To override
	 * Send the 2FA code to the user (email, sms, phone call...)
	 * @param string $code
	 * @param mixed $connected
	 */
	protected function _send2FACode(string $code,$connected):void{

	}

	/**
	 * To override
	 * Returns the default size for generated tokens.
	 * @return int
	 */
	protected function getTokenSize():int{
		return 6;
	}

	/**
	 * Generates a new random 2FA code.
	 * You have to override this basic implementation.
	 * @return string
	 * @throws \Exception
	 */
	protected function generate2FACode():string{
		return \bin2hex ( \random_bytes ($this->getTokenSize()));
	}

	/**
	 * Returns the code prefix (which should not be entered by the user).
	 * @return string
	 */
	protected function towFACodePrefix():string{
		return 'U-';
	}


	/**
	 * Returns the default validity duration of a generated 2FA code.
	 * @return \DateInterval
	 */
	protected function twoFACodeDuration():\DateInterval{
		return new \DateInterval('PT5M');
	}

	/**
	 * To override for modifying the 2FA panel message.
	 * @param FlashMessage $fMessage
	 */
	protected function twoFAMessage(FlashMessage $fMessage){

	}
	/**
	 * To override
	 * @param FlashMessage $fMessage
	 */
	protected function newTwoFACodeMessage(FlashMessage $fMessage){

	}

	/**
	 * To override for modifying the message displayed if the 2FA code is bad.
	 * @param FlashMessage $fMessage
	 */
	protected function twoFABadCodeMessage(FlashMessage $fMessage){

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

	protected function send2FACode(){
		$codeInfos=$this->save2FACode();
		$this->_send2FACode($codeInfos['code'], USession::get($this->_getUserSessionKey().'-2FA'));
	}
	
	public function sendNew2FACode(){
		if(USession::exists( $this->_getUserSessionKey().'-2FA')) {
			$this->send2FACode();
			$fMessage = new FlashMessage ('A new code was submited.', 'Two factor Authentification', 'success', 'key');
			$this->newTwoFACodeMessage($fMessage);
			echo $this->fMessage($fMessage);
		}
	}

}

