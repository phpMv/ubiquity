.. _auth:
Auth Controllers
================


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
	public function _isValidUser($action=null) {
		return USession::exists($this->_getUserSessionKey());
	}

	public function _getBaseRoute() {
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
	public function _isValidUser($action=null) {
		return USession::exists($this->_getUserSessionKey());
	}

	public function _getBaseRoute() {
		return 'BaseAuthController';
	}
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\auth\AuthController::_getLoginInputName()
	 */
	public function _getLoginInputName() {
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
		return new BaseAuthController();
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
   	public function _displayInfoAsString() {
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

Example with the **_userInfo** aera:

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
		return new PersoAuthController();
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
    protected function attemptsNumber() {
        return 3;
    }
   ...
   }
   


