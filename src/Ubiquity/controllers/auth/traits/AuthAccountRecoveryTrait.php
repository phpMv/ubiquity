<?php

namespace Ubiquity\controllers\auth\traits;

use Ajax\semantic\html\collections\form\HtmlForm;
use Ubiquity\controllers\auth\AuthFiles;
use Ubiquity\controllers\auth\AuthTokens;
use Ubiquity\utils\base\UDateTime;
use Ubiquity\utils\flash\FlashMessage;
use Ubiquity\utils\http\URequest;

/**
 * 
 * Ubiquity\controllers\auth\traits$AuthAccountRecoveryTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 * 
 */
trait AuthAccountRecoveryTrait {

	protected static string $TOKENS_RECOVERY_ACCOUNT='account.recovery';

	abstract protected function fMessage(FlashMessage $fMessage, $id = null):string;

	abstract protected function _getFiles(): AuthFiles;

	abstract protected function getBaseUrl():string;

	abstract protected function authLoadView($viewName, $vars = [ ]):void;

	abstract protected function useAjax():bool;

	abstract public function _getBodySelector():string;

	abstract public function _addFrmAjaxBehavior($id):HtmlForm;

	abstract public function _getPasswordInputName():string;

	abstract protected function passwordConfLabel():string;

	abstract protected function passwordLabel():string;

	abstract public function info($force = null);

	abstract protected function validateEmail(string $mail):bool;

	/**
	 * @return bool
	 */
	protected function hasAccountRecovery():bool{
		return false;
	}

	/**
	 * To override
	 * Displayed when an account recovery operation is initiated.
	 * @param FlashMessage $fMessage
	 */
	protected function recoveryInitMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when email is sent for a recovery account operation.
	 * @param FlashMessage $fMessage
	 */
	protected function recoveryEmailSendMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when email is not associated with an existing account.
	 * @param FlashMessage $fMessage
	 */
	protected function recoveryEmailErrorMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when a new password is set with recovery account.
	 * @param FlashMessage $fMessage
	 */
	protected function resetPasswordSuccessMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when an error occurs when a new password is set with recovery account.
	 * @param FlashMessage $fMessage
	 */
	protected function resetPasswordErrorMessage(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when the account recovery link is valid.
	 * @param FlashMessage $fMessage
	 */
	protected function emailAccountRecoverySuccess(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when the account recovery link is not valid.
	 * @param FlashMessage $fMessage
	 */
	protected function emailAccountRecoveryError(FlashMessage $fMessage){

	}

	/**
	 * Returns the recovery account link caption.
	 * Default : Forgot your password?
	 * @return string
	 */
	protected function recoveryAccountCaption():string{
		return 'Forgot your password?';
	}

	/**
	 * Returns the default validity duration for an email account recovery.
	 * @return \DateInterval
	 */
	protected function accountRecoveryDuration():\DateInterval{
		return new \DateInterval('PT30M');
	}

	/**
	 * To override
	 * Returns the AuthTokens instance used for tokens generation for a recovery account.
	 * @return AuthTokens
	 */
	protected function getAuthTokensAccountRecovery():AuthTokens{
		return new AuthTokens(self::$TOKENS_RECOVERY_ACCOUNT,10,$this->accountRecoveryDuration()->s,true);
	}

	/**
	 * To override
	 * Checks if a valid account matches this email.
	 * @param string $email
	 * @return bool
	 */
	protected function isValidEmailForRecovery(string $email):bool {
		return true;
	}

	/**
	 * Sends an email for account recovery (password reset).
	 * @param string $email
	 * @param string $validationURL
	 * @param string $expire
	 * @return boolean
	 */
	protected function _sendEmailAccountRecovery(string $email,string $validationURL,string $expire):bool{
		return false;
	}

	/**
	 * To override
	 * Changes the active password associated with the account corresponding to this email.
	 * @param string $email
	 * @param string $newPasswordHash
	 * @return bool
	 */
	protected function passwordResetAction(string $email,string $newPasswordHash):bool{
		return false;
	}

	protected function getAccountRecoveryLink():string{
		$href=$this->getBaseUrl().'/recoveryInit';
		$target=$this->_getBodySelector();
		$caption=$this->recoveryAccountCaption();
		return "<a class='_recovery' href='$href' data-target='$target'>$caption</a>";
	}

	public function recoveryInit(){
		$fMessage = new FlashMessage( 'Enter the email associated with your account to receive a password reset link.', 'Account recovery', 'info', 'user' );
		$this->recoveryInitMessage ( $fMessage );
		$message = $this->fMessage ( $fMessage );
		if($this->useAjax()){
			$frm=$this->jquery->semantic()->htmlForm('frm-account-recovery');
			$frm->addExtraFieldRules('email',['empty','email']);
			$frm->setValidationParams(['inline'=>true,'on'=>'blur']);
		}
		$this->authLoadView ( $this->_getFiles ()->getViewInitRecovery(), [ '_message' => $message,'submitURL' => $this->getBaseUrl ().'/recoveryInfo','bodySelector' => $this->_getBodySelector()] );
	}

	/**
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function recoveryInfo(){
		if(URequest::isPost()){
			if($this->isValidEmailForRecovery($email=URequest::filterPost('email',FILTER_VALIDATE_EMAIL))) {
				$this->prepareEmailAccountRecovery($email);
				$fMessage = new FlashMessage (sprintf('A password reset email has been sent to <b>%s</b>.<br>You can only use this link temporarily, from the same machine, on this browser.',$email), 'Account recovery', 'success', 'email');
				$this->recoveryEmailSendMessage($fMessage);
			}else{
				$fMessage = new FlashMessage (sprintf('No account is associated with the email address <b>%s</b>.<br><a href="%s" data-target="%s">Try again.</a>.',$email,$this->getBaseUrl().'/recoveryInit',$this->_getBodySelector()), 'Account recovery', 'error', 'user');
				$this->recoveryEmailErrorMessage($fMessage);
			}
			echo $this->fMessage ( $fMessage );
		}
	}

	public function recovery(string $key,string $hashMail) {
		$tokens = $this->getAuthTokensAccountRecovery();
		if ($tokens->exists($key)) {
			if (!$tokens->expired($key)) {
				$data = $tokens->fetch($key);
				if(\is_array($data)) {
					$email = $data['email'];
					if (\md5($email) === $hashMail && $this->validateEmail($email)) {
						$fMessage = new FlashMessage ("Enter a new password associated to the account <b>$email</b>.", 'Account recovery', 'success', 'user');
						$this->emailAccountRecoverySuccess($fMessage);
						$message=$this->fMessage($fMessage);
						if($this->useAjax()) {
							$frm = $this->_addFrmAjaxBehavior('frm-account-recovery');
							$passwordInputName = $this->_getPasswordInputName();
							$frm->addExtraFieldRules($passwordInputName . '-conf', ['empty', "match[$passwordInputName]"]);
						}
						$this->authLoadView ( $this->_getFiles ()->getViewRecovery(), [ 'key'=>$key,'email'=>$email,'_message' => $message,'submitURL' => $this->getBaseUrl ().'/recoverySubmit','bodySelector' => $this->_getBodySelector(),'passwordInputName' => $this->_getPasswordInputName (),'passwordLabel' => $this->passwordLabel (),'passwordConfLabel'=>$this->passwordConfLabel()] );
						return ;
					}
				}
				$msg = 'This recovery link was not generated on this device!';
			} else {
				$msg = 'This recovery link is no longer active!';
			}
		}
		$fMessage = new FlashMessage ($msg ?? 'This account recovery link is not valid!', 'Account recovery', 'error', 'user');
		$this->emailAccountRecoveryError($fMessage);
		echo $this->fMessage($fMessage);
	}

	protected function generateEmailAccountRecoveryUrl($email):array {
		$duration=$this->accountRecoveryDuration();
		$tokens=$this->getAuthTokensAccountRecovery();
		$d=new \DateTime();
		$dExpire=$d->add($duration);
		$key=$tokens->store(['email'=>$email]);
		return ['url'=>$key.'/'.\md5($email),'expire'=>$dExpire];
	}

	protected function prepareEmailAccountRecovery(string $email){
		$data=$this->generateEmailAccountRecoveryUrl($email);
		$validationURL=$this->getBaseUrl().'/recovery/'.$data['url'];
		$this->_sendEmailAccountRecovery($email, $validationURL,UDateTime::elapsed($data['expire']));
	}

	/**
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function recoverySubmit(){
		if(URequest::isPost() && URequest::has('key')){
			$isValid=false;
			$msg='This account recovery link is invalid!';
			$tokens = $this->getAuthTokensAccountRecovery();
			$key=URequest::post('key');
			if ($tokens->exists($key)) {
				if(!$tokens->expired($key)){
					$data=$tokens->fetch($key);
					$email=$data['email'];
					if($email===URequest::post('email')){
						if($this->passwordResetAction($email,URequest::password_hash('password'))){
							$fMessage = new FlashMessage ("Your password has been updated correctly for the account associated with <b>$email</b>.", 'Account recovery', 'success', 'user');
							$this->resetPasswordSuccessMessage($fMessage);
							echo $this->info(true);
							$isValid=true;
						}else{
							$msg='An error occurs when updating your password!';
						}
					}
				}else{
					$msg='This account recovery link is expired!';
				}
				$tokens->remove($key);
			}
			if(!$isValid){
				$fMessage = new FlashMessage ($msg, 'Account recovery', 'error', 'user');
				$this->resetPasswordErrorMessage($fMessage);
			}
			echo $this->fMessage($fMessage);
		}
	}

}

