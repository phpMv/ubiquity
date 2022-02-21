.. _auth:
Auth Controllers
================

.. |br| raw:: html

   <br />

The Auth controllers allow you to perform basic authentification with:
 - login with an account
 - account creation
 - logout
 - controllers with required authentication 
 
Creation
--------
 In the admin interface (web-tools), activate the **Controllers** part, and choose create **Auth controller**:

.. image:: /_static/images/crud/speControllerBtn.png

Then fill in the form:
  - Enter the controller name (BaseAuthController in this case)
 
.. image:: /_static/images/auth/createAuthForm1.png

The generated controller:

.. code-block:: php
   :linenos:
   :caption: app/controllers/BaseAuthController.php
   
    /**
    * Auth Controller BaseAuthController
    **/
   class BaseAuthController extends \Ubiquity\controllers\auth\AuthController{

	protected function onConnect($connected) {
		$urlParts=$this->getOriginalURL();
		USession::set($this->_getUserSessionKey(), $connected);
		if(isset($urlParts)){
			Startup::forward(implode("/",$urlParts));
		}else{
			//TODO
			//Forwarding to the default controller/action
		}
	}

	protected function _connect() {
		if(URequest::isPost()){
			$email=URequest::post($this->_getLoginInputName());
			$password=URequest::post($this->_getPasswordInputName());
			//TODO
			//Loading from the database the user corresponding to the parameters
			//Checking user creditentials
			//Returning the user
		}
		return;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\auth\AuthController::isValidUser()
	 */
	public function _isValidUser($action=null): bool {
		return USession::exists($this->_getUserSessionKey());
	}

	public function _getBaseRoute(): string {
		return 'BaseAuthController';
	}
   }
   
Implementation of the authentification
--------------------------------------
Example of implementation with the administration interface : We will add an authentication check on the admin interface.

Authentication is based on verification of the email/password pair of a model **User**:

.. image:: /_static/images/auth/model-user.png


BaseAuthController modification
*******************************

.. code-block:: php
   :linenos:
   :caption: app/controllers/BaseAuthController.php
   :emphasize-lines: 12,20,41

    /**
    * Auth Controller BaseAuthController
    **/
   class BaseAuthController extends \Ubiquity\controllers\auth\AuthController{

	protected function onConnect($connected) {
		$urlParts=$this->getOriginalURL();
		USession::set($this->_getUserSessionKey(), $connected);
		if(isset($urlParts)){
			Startup::forward(implode("/",$urlParts));
		}else{
			Startup::forward("admin");
		}
	}

	protected function _connect() {
		if(URequest::isPost()){
			$email=URequest::post($this->_getLoginInputName());
			$password=URequest::post($this->_getPasswordInputName());
			return DAO::uGetOne(User::class, "email=? and password= ?",false,[$email,$password]);
		}
		return;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\auth\AuthController::isValidUser()
	 */
	public function _isValidUser($action=null): bool {
		return USession::exists($this->_getUserSessionKey());
	}

	public function _getBaseRoute(): string {
		return 'BaseAuthController';
	}
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\auth\AuthController::_getLoginInputName()
	 */
	public function _getLoginInputName(): string {
		return "email";
	}
   }

Admin controller modification
*****************************

Modify the Admin Controller to use BaseAuthController:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Admin.php
   :emphasize-lines: 2-5
   
   class Admin extends UbiquityMyAdminBaseController{
	use WithAuthTrait;
	protected function getAuthController(): AuthController {
		return $this->_auth ??= new BaseAuthController($this);
	}
   }

Test the administration interface at **/admin**:

.. image:: /_static/images/auth/adminForbidden.png

After clicking on **login**:

.. image:: /_static/images/auth/formLogin.png

If the authentication data entered is invalid:

.. image:: /_static/images/auth/invalidCreditentials.png

If the authentication data entered is valid:

.. image:: /_static/images/auth/adminWithAuth.png

Attaching the zone info-user
****************************

Modify the **BaseAuthController** controller:

.. code-block:: php
   :linenos:
   :caption: app/controllers/BaseAuthController.php
   :emphasize-lines: 6-8

    /**
    * Auth Controller BaseAuthController
    **/
   class BaseAuthController extends \Ubiquity\controllers\auth\AuthController{
   ...
   	public function _displayInfoAsString(): bool {
		return true;
	}
   }

The **_userInfo** area is now present on every page of the administration:

.. image:: /_static/images/auth/infoUserZone.png

It can be displayed in any twig template:

.. code-block:: twig

   {{ _userInfo | raw }}


Description of the features
---------------------------

Customizing templates
*********************

index.html template
###################

The index.html template manages the connection:

.. image:: /_static/images/auth/template_authIndex.png

Example with the **_userInfo** area:

Create a new AuthController named **PersoAuthController**:

.. image:: /_static/images/auth/createAuthForm2.png

Edit the template **app/views/PersoAuthController/info.html**

.. code-block:: twig
   :linenos:
   :caption: app/views/PersoAuthController/info.html
   :emphasize-lines: 3,21
   
   {% extends "@framework/auth/info.html" %}
   {% block _before %}
   	<div class="ui tertiary inverted red segment">
   {% endblock %}
   {% block _userInfo %}
   	{{ parent() }}
   {% endblock %}
   {% block _logoutButton %}
   	{{ parent() }}
   {% endblock %}
   {% block _logoutCaption %}
   	{{ parent() }}
   {% endblock %}
   {% block _loginButton %}
   	{{ parent() }}
   {% endblock %}
   {% block _loginCaption %}
   	{{ parent() }}
   {% endblock %}
   {% block _after %}
   		</div>
   {% endblock %}

Change the AuthController **Admin** controller: 

.. code-block:: php
   :linenos:
   :caption: app/controllers/Admin.php
   :emphasize-lines: 2-5
   
   class Admin extends UbiquityMyAdminBaseController{
	use WithAuthTrait;
	protected function getAuthController(): AuthController {
		return $this->_auth ??= new PersoAuthController($this);
	}
   }


.. image:: /_static/images/auth/adminWithAuth2.png


Customizing messages
********************

.. code-block:: php
   :linenos:
   :caption: app/controllers/PersoAuthController.php
   
   class PersoAuthController extends \controllers\BaseAuth{
   ...
    /**
     * {@inheritDoc}
     * @see \Ubiquity\controllers\auth\AuthController::badLoginMessage()
     */
    protected function badLoginMessage(\Ubiquity\utils\flash\FlashMessage $fMessage) {
        $fMessage->setTitle("Erreur d'authentification");
        $fMessage->setContent("Login ou mot de passe incorrects !");
        $this->_setLoginCaption("Essayer à nouveau");
         
    }
   ...
   }

Self-check connection
*********************

.. code-block:: php
   :linenos:
   :caption: app/controllers/PersoAuthController.php
   
   class PersoAuthController extends \controllers\BaseAuth{
   ...
    /**
     * {@inheritDoc}
     * @see \Ubiquity\controllers\auth\AuthController::_checkConnectionTimeout()
     */
    public function _checkConnectionTimeout() {
        return 10000;
    }
   ...
   }

Limitation of connection attempts
*********************************

.. code-block:: php
   :linenos:
   :caption: app/controllers/PersoAuthController.php
   
   class PersoAuthController extends \controllers\BaseAuth{
   ...
    /**
     * {@inheritDoc}
     * @see \Ubiquity\controllers\auth\AuthController::attemptsNumber()
     */
    protected function attemptsNumber(): int {
        return 3;
    }
   ...
   }
   

Account recovery
****************

account recovery is used to reset the account password. |br|
A password reset email is sent, to an email address corresponding to an active account.

.. image:: /_static/images/auth/recoveryInit.png

.. code-block:: php
   :linenos:
   :caption: app/controllers/PersoAuthController.php

   class PersoAuthController extends \controllers\BaseAuth{
   ...
    protected function hasAccountRecovery():bool{
        return true;
    }

    protected function _sendEmailAccountRecovery(string $email,string $validationURL,string $expire):bool {
        MailerManager::start();
        $mail=new AuthAccountRecoveryMail();
        $mail->to($connected->getEmail());
        $mail->setUrl($validationURL);
        $mail->setExpire($expire);
        return MailerManager::send($mail);
    }

    protected function passwordResetAction(string $email,string $newPasswordHash):bool {
        //To implement for modifying the user password
    }

    protected function isValidEmailForRecovery(string $email):bool {
        //To implement: return true if a valid account match with this email
    }
   }

.. image:: /_static/images/auth/recoveryForm.png

.. note::
    By default, the link can only be used on the same machine, within a predetermined period of time (which can be modified by overriding the ``accountRecoveryDuration`` method).

Activation of MFA/2FA
**********************
Multi-factor authentication can be enabled conditionally, based on the pre-logged-in user's information.

.. note::
	Phase 2 of the authentication is done in the example below by sending a random code by email.
	The AuthMailerClass class is available in the ``Ubiquity-mailer`` package.

.. code-block:: php
   :linenos:
   :caption: app/controllers/PersoAuthController.php
   
   class PersoAuthController extends \controllers\BaseAuth{
   ...
    /**
     * {@inheritDoc}
     * @see \Ubiquity\controllers\auth\AuthController::has2FA()
     */
    protected function has2FA($accountValue=null):bool{
        return true;
    }
    
    protected function _send2FACode(string $code, $connected):void {
        MailerManager::start();
        $mail=new AuthMailerClass();
        $mail->to($connected->getEmail());
        $mail->setCode($code);
        MailerManager::send($mail);
    }
   ...
   }
   

.. image:: /_static/images/auth/2fa-code.png


.. note::
	It is possible to customize the creation of the generated code, as well as the prefix used.
	The sample below is implemented with ``robthree/twofactorauth`` library.

.. code-block:: php
   
   	protected function generate2FACode():string{
   		$tfa=new TwoFactorAuth();
   		return $tfa->createSecret();
   	}
   	
   	protected function towFACodePrefix():string{
   		return 'U-';
   	}
   

Account creation
****************

The activation of the account creation is also optional:

.. image:: /_static/images/auth/account-creation-available.png

.. code-block:: php
   :linenos:
   :caption: app/controllers/PersoAuthController.php
   
   class PersoAuthController extends \controllers\BaseAuth{
   ...
    protected function hasAccountCreation():bool{
        return true;
    }
   ...
   }
   

.. image:: /_static/images/auth/account-creation.png

In this case, the _create method must be overridden in order to create the account:

.. code-block:: php
   
   	protected function _create(string $login, string $password): ?bool {
   		if(!DAO::exists(User::class,'login= ?',[$login])){
   			$user=new User();
   			$user->setLogin($login);
   			$user->setPassword($password);
   			URequest::setValuesToObject($user);//for the others params in the POST.
   			return DAO::insert($user);
   		}
   		return false;
   	}
   

You can check the validity/availability of the login before validating the account creation form:

.. code-block:: php
   
   	protected function newAccountCreationRule(string $accountName): ?bool {
   		return !DAO::exists(User::class,'login= ?',[$accountName]);
   	}
   

.. image:: /_static/images/auth/account-creation-error.png

A confirmation action (email verification) may be requested from the user:

.. code-block:: php
   
   	protected function hasEmailValidation(): bool {
   		return true;
   	}
   
   	protected function _sendEmailValidation(string $email,string $validationURL,string $expire):void {
   		MailerManager::start();
   		$mail=new AuthEmailValidationMail();
   		$mail->to($connected->getEmail());
   		$mail->setUrl($validationURL);
   		$mail->setExpire($expire);
   		MailerManager::send($mail);
   	}

.. note::
	It is possible to customize these parts by overriding the associated methods, or by modifying the interfaces in the concerned templates.

