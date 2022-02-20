<?php

namespace Ubiquity\controllers\auth\traits;

use Ajax\semantic\components\validation\Rule;
use Ajax\semantic\html\collections\form\HtmlForm;
use Ubiquity\controllers\auth\AuthFiles;
use Ubiquity\controllers\auth\AuthTokens;
use Ubiquity\utils\base\UDateTime;
use Ubiquity\utils\flash\FlashMessage;
use Ubiquity\utils\http\URequest;

/**
 * Trait AuthAccountCreationTrait
 *
 */
trait AuthAccountCreationTrait {

	protected static string $TOKENS_VALIDATE_EMAIL='email.validation';

	abstract protected function getBaseUrl():string;

	abstract protected function fMessage(FlashMessage $fMessage, $id = null):string;

	abstract protected function useAjax():bool;

	abstract public function _addFrmAjaxBehavior($id):HtmlForm;

	abstract public function _getPasswordInputName():string;

	abstract public function _getLoginInputName():string;

	abstract protected function authLoadView($viewName, $vars = [ ]):void;

	abstract protected function rememberCaption():string;

	abstract protected function loginLabel():string;

	abstract protected function passwordConfLabel(): string;

	abstract protected function passwordLabel(): string;

	abstract protected function _getFiles(): AuthFiles;

	abstract public function _getBodySelector():string;

	/**
	 * Returns true for account creation.
	 * @return boolean
	 */
	protected function hasAccountCreation():bool{
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function hasEmailValidation():bool{
		return false;
	}

	/**
	 * Returns the default validity duration of a mail validation link.
	 * @return \DateInterval
	 */
	protected function emailValidationDuration():\DateInterval{
		return new \DateInterval('PT24H');
	}
	/**
	 * To override for modifying the account creation message.
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function createAccountMessage(FlashMessage $fMessage) {
	}

	/**
	 * To override for modifying the account creation message information.
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function canCreateAccountMessage(FlashMessage $fMessage) {
	}

	/**
	 * To override for modifying the error for account creation.
	 *
	 * @param FlashMessage $fMessage
	 */
	protected function createAccountErrorMessage(FlashMessage $fMessage) {
	}

	/**
	 * To override
	 * Displayed when email is valid.
	 * @param FlashMessage $fMessage
	 */
	protected function emailValidationSuccess(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * Displayed when email is invalid or if an error occurs.
	 * @param FlashMessage $fMessage
	 */
	protected function emailValidationError(FlashMessage $fMessage){

	}

	/**
	 * To override
	 * For creating a new user account.
	 */
	protected function _create(string $login,string $password):?bool{
		return false;
	}
	
	/**
	 * To override
	 * Returns true if the creation of $accountName is possible.
	 * @param string $accountName
	 * @return bool
	 */
	protected function _newAccountCreationRule(string $accountName):?bool{
		
	}
	
	/**
	 * Sends an email for email checking.
	 * @param string $email
	 * @param string $validationURL
	 * @param string $expire
	 */
	protected function _sendEmailValidation(string $email,string $validationURL,string $expire):void{
		
	}
	
	/**
	 * To override
	 * Returns the email from an account object.
	 * @param mixed $account
	 * @return string
	 */
	protected function getEmailFromNewAccount($account):string{
		return $account;
	}

	/**
	 * To override
	 * Returns the AuthTokens instance used for tokens generation when sending an email for the account creation.
	 * @return AuthTokens
	 */
	protected function getAuthTokensEmailValidation():AuthTokens{
		return new AuthTokens(self::$TOKENS_VALIDATE_EMAIL,10,$this->emailValidationDuration()->s,false);
	}

	protected function generateEmailValidationUrl($email):array {
		$duration=$this->emailValidationDuration();
		$tokens=$this->getAuthTokensEmailValidation();
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
	 * Route for email validation checking when creating a new account.
	 * @param string $key
	 * @param string $hashMail
	 */
	public function checkEmail(string $key,string $hashMail){
		$isValid=false;
		$tokens=$this->getAuthTokensEmailValidation();
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

	/**
	 * Displays the account creation form.
	 * Form is submited to /createAccount action
	 */
	public function addAccount(){
		if($this->hasAccountCreation()){
			if($this->useAjax()){
				$frm=$this->_addFrmAjaxBehavior('frm-create');
				$passwordInputName=$this->_getPasswordInputName();
				$frm->addExtraFieldRules($passwordInputName.'-conf', ['empty',"match[$passwordInputName]"]);
				if($this->_newAccountCreationRule('')!==null){
					$this->jquery->exec(Rule::ajax($this->jquery, 'checkAccount', $this->getBaseUrl () . '/newAccountCreationRule', '{}', 'result=data.result;', 'postForm', [
						'form' => 'frm-create'
					]), true);
					$frm->addExtraFieldRule($this->_getLoginInputName(), 'checkAccount','Account {value} is not available!');
				}
			}
			$this->authLoadView ( $this->_getFiles ()->getViewCreate(), [ 'action' => $this->getBaseUrl () . '/createAccount','loginInputName' => $this->_getLoginInputName (),'loginLabel' => $this->loginLabel (),'passwordInputName' => $this->_getPasswordInputName (),'passwordLabel' => $this->passwordLabel (),'passwordConfLabel'=>$this->passwordConfLabel(),'rememberCaption' => $this->rememberCaption () ] );
		}
	}


	/**
	 * Submit for a new account creation.
	 *
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function createAccount(){
		$account=URequest::post($this->_getLoginInputName());
		$msgSup='';
		if($this->_create($account,URequest::post($this->_getPasswordInputName()))){
			if($this->hasEmailValidation()){
				$email=$this->getEmailFromNewAccount($account);
				$this->prepareEmailValidation($email);
				$msgSup="<br>Confirm your email address <b>$email</b> by checking your mailbox.";
			}
			$msg=new FlashMessage ( '<b>{account}</b> account created with success!'.$msgSup, 'Account creation', 'success', 'check square' );
		}else{
			$msg=new FlashMessage ( 'The account <b>{account}</b> was not created!', 'Account creation', 'error', 'warning circle' );
		}
		$message=$this->fMessage($msg->parseContent(['account'=>$account]));
		$this->authLoadView ( $this->_getFiles ()->getViewNoAccess (), [ '_message' => $message,'authURL' => $this->getBaseUrl (),'bodySelector' => $this->_getBodySelector (),'_loginCaption' => $this->_loginCaption ] );
	}
}

