.. _oauth:
OAuth2 client module
====================
.. |br| raw:: html

   <br />

.. note:: This part is accessible from the **webtools**, so if you created your project with the **-a** option or with the **create-project** command.
          The OAuth module is not installed by default. It uses HybridAuth library.


Installation
------------

In the root of your project:

.. code-block:: bash
   
	composer require phpmv/ubiquity-oauth

.. note:: It is also possible to add the **ubiquity-oauth** dependency using the **Composer** part of the administration module.

.. image:: /_static/images/composer/composer-add-1.png
   :class: bordered

OAuth configuration
-------------------

Global configuration
++++++++++++++++++++

.. image:: /_static/images/oauth/oauth-part-0.png
   :class: bordered

Click on the **Global configuration** button, and modify the callback URL, which corresponds to the local callback url after a successful connection.

.. image:: /_static/images/oauth/oauth-part-callback.png
   :class: bordered

OAuth controller
++++++++++++++++

Click on the **Create Oauth controller** button and assign to the route the value previously given to the callback:

.. image:: /_static/images/oauth/create-oauth-controller.png
   :class: bordered

Validate and reset the router cache:

.. image:: /_static/images/oauth/create-oauth-controller-created.png
   :class: bordered

Providers
+++++++++

.. note:: For an OAuth authentication, it is necessary to create an application at the provider beforehand, and to take note of the keys of the application (id and secret).

Click on the **Add provider** button and select **Google**:

.. image:: /_static/images/oauth/provider-config.png
   :class: bordered

Check the connection by clicking on the **Check** button:

.. image:: /_static/images/oauth/google-check.png
   :class: bordered
   
Post Login Information:

.. image:: /_static/images/oauth/google-check-infos.png
   :class: bordered

OAuthController customization
-----------------------------
The controller created is the following:

.. code-block:: php
   :caption: app/controllers/OAuthTest.php
   
   namespace controllers;
   use Hybridauth\Adapter\AdapterInterface;
   /**
    * Controller OAuthTest
    */
   class OAuthTest extends \Ubiquity\controllers\auth\AbstractOAuthController{

      public function index(){
      }

      /**
       * @get("oauth/{name}")
       */
      public function _oauth(string $name):void {
         parent::_oauth($name);
      }

      protected function onConnect(string $name,AdapterInterface $provider){
         //TODO
      }
   }

- The **_oauth** method corresponds to the callback url
- The **onConnect** method is triggered on connection and can be overridden.


Example :

- Possible retrieval of an associated user in the database
- or creation of a new user
- Adding the logged-in user and redirection

.. code-block:: php
   :caption: app/controllers/OAuthTest.php
   
      protected function onConnect(string $name, AdapterInterface $provider) {
         $userProfile = $provider->getUserProfile();
         $key = md5($name . $userProfile->identifier);
         $user = DAO::getOne(User::class, 'oauth= ?', false, [
            $key
         ]);
         if (! isset($user)) {
            $user = new User();
            $user->setOauth($key);
            $user->setLogin($userProfile->displayName);
            DAO::save($user);
         }
         USession::set('activeUser', $user);
         \header('location:/');
   	}
