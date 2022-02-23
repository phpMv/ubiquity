<?php

namespace Ubiquity\controllers\auth;

use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UConfigFile;

/**
 * 
 * Ubiquity\controllers\auth$AuthControllerConfig
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
abstract class AuthControllerConfig extends AuthController {
	
	protected array $config;

	public function initialize(){
		$this->_init();
		parent::initialize();
	}
	
	public function _init(){
		$file=new UConfigFile($this->getConfigFilename());
		$this->config=$file->load();
	}

	abstract protected function getConfigFilename():string;

	protected function useAjax():bool{
		return $this->config['useAjax']??true;
	}

	protected function attemptsNumber() {
		return $this->config['attemptsNumber']??null;
	}

	public function _getUserSessionKey(): string {
		return $this->config['userSessionKey']??'activeUser';
	}

	public function attemptsTimeout():int{
		return $this->config['attemptsTimeout']??(3*60);
	}

	public function _displayInfoAsString():bool{
		return $this->config['displayInfoAsString']??false;
	}

	public function _getLoginInputName():string{
		return $this->config['loginInputName']??'email';
	}

	public function loginLabel():string{
		return $this->config['loginLabel']??\ucfirst($this->_getLoginInputName());
	}

	public function _getPasswordInputName():string{
		return $this->config['passwordInputName']??'password';
	}

	protected function passwordLabel(): string {
		return $this->config['passwordLabel']??\ucfirst ( $this->_getPasswordInputName () );
	}

	protected function passwordConfLabel(): string {
		return $this->config['passwordConfLabel']??(\ucfirst ( $this->_getPasswordInputName () ).' confirmation');
	}

	public function _getBodySelector():string {
		return $this->config['bodySelector']??'main .container';
	}

	protected function rememberCaption():string {
		return $this->config['rememberCaption']??'Remember me';
	}

	protected function getTokenSize():int{
		return $this->config['2FA.tokenSize']??6;
	}

	protected function towFACodePrefix():string{
		return $this->config['2FA.codePrefix']??'U-';
	}

	protected function hasAccountCreation():bool{
		return $this->config['hasAccountCreation']??false;
	}

	protected function hasAccountRecovery():bool{
		return $this->config['hasAccountRecovery']??false;
	}

	protected function recoveryAccountCaption():string{
		return $this->config['recoveryAccountCaption']??'Forgot your password?';
	}

	public static function init(?string $name=null,?array $config=null){
		$config??=[
			'attempsNumber'=>null,
			'userSessionKey'=>'activeUser',
			'useAjax'=>true,
			'attemptsTimeout'=>3*60,
			'displayInfoAsString'=>false,
			'loginInputName'=>'email',
			'loginLabel'=>'Email',
			'passwordInputName'=>'password',
			'passwordLabel'=>'Password',
			'passwordConfLabel'=>'Password confirmation',
			'rememberCaption'=>'Remember me',
			'bodySelector'=>'main .container',
			'2FA.tokenSize'=>6,
			'2FA.codePrefix'=>'U-',
			'hasAccountCreation'=>false,
			'hasAccountRecovery'=>false,
			'recoveryAccountCaption'=>'Forgot your password?'

		];
		$name??=\lcfirst(ClassUtils::getClassSimpleName(static::class));
		$file=new UConfigFile($name);
		$file->setData($config);
		$file->save();
	}

}

