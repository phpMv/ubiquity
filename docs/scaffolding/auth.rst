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
	public function _isValidUser() {
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



.. code-block:: php
   :linenos:
   :caption: app/controllers/BaseAuthController.php
   :emphasize-lines: 12,20,39

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
	public function _isValidUser() {
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

Modify the Admin Controller to use BaseAuthController:

.. code-block:: php
   :linenos:
   :caption: app/controllers/BaseAuthController.php
   :emphasize-lines: 2-5
   
   class Admin extends UbiquityMyAdminBaseController{
	use WithAuthTrait;
	protected function getAuthController(): AuthController {
		return new BaseAuthController();
	}
   }

Description of the features
---------------------------
Test the created controller by clicking on the get button in front of the **index** action: