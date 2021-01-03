.. _securityModule:
Security module
***************

.. |br| raw:: html

   <br />

Installation
============

Install the Ubiquity-security module from the command prompt or from the **Webtools** (Composer part).

.. code-block:: bash

    composer require phpmv/ubiquity-security

Then activate the display of the Security part in the **Webtools**:

.. image:: /_static/images/security/display-security.png
   :class: bordered

Session CSRF
============

The session is by default protected against CSRF attacks via the ``VerifyCsrfToken`` class (even without the **Ubiquity-security** module). |br|
A token instance (``CSRFToken``) is generated at the session startup. The validity of the token is then checked via a cookie at each request.

.. image:: /_static/images/security/security-part.png
   :class: bordered

This protection can be customized by creating a class implementing the ``VerifySessionCsrfInterface``.

.. code-block:: php
   :caption: app/session/MyCsrfProtection.php

   class MyCsrfProtection implements VerifySessionCsrfInterface {
      private AbstractSession $sessionInstance;

      public function __construct(AbstractSession $sessionInstance) {
         $this->sessionInstance = $sessionInstance;
      }

      public function init() {
         //TODO when the session starts
      }

      public function clear() {
         //TODO when the session ends
      }

      public function start() {
         //TODO When the session starts or is resumed
      }

      public static function getLevel() {
         return 1; //An integer to appreciate the level of security
      }
   }

Starting the custom protection in services:

.. code-block:: php
   :caption: app/config/services.php

   use Ubiquity\utils\http\session\PhpSession;
   use Ubiquity\controllers\Startup;
   use app\session\MyCsrfProtection;

   Startup::setSessionInstance(new PhpSession(new MyCsrfProtection()));

Deactivating the protection
^^^^^^^^^^^^^^^^^^^^^^^^^^^
If you do not need to protect your session against Csrf attacks, start the session with the ``NoCsrfProtection`` class.

.. code-block:: php
   :caption: app/config/services.php

   use Ubiquity\utils\http\session\PhpSession;
   use Ubiquity\controllers\Startup;
   use Ubiquity\utils\http\session\protection\NoCsrfProtection;

   Startup::setSessionInstance(new PhpSession(new NoCsrfProtection()));

CSRF manager
============
The **CsrfManager** service can be started directly from the **webtools** interface. |br|
Its role is to provide tools to protect sensitive routes from Csrf attacks (the ones that allow the validation of forms for example).

.. image:: /_static/images/security/csrf-manager-started.png
   :class: bordered

- The service is started in the ``services.php`` file.

.. code-block:: php
   :caption: app/config/services.php

    \Ubiquity\security\csrf\CsrfManager::start();

Example of form protection:
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The form view:

.. code-block:: html+twig

   <form id="frm-bar" action='/submit' method='post'>
      {{ csrf('frm-bar') }}
      <input type='text' id='sensitiveData' name='sensitiveData'>
   </form>

The ``csrf`` method generates a token for the form.

The form submitting in a controller:

.. code-block:: php

   use Ubiquity\security\csrf\UCsrfHttp;

   #[Post('/submit')]
   public function submit(){
      if(UCsrfHttp::isValidPost('frm-bar')){
         //Token is valid! => do something with post datas
      }
   }

.. note:: It is also possible to manage this protection via cookie, or meta tag in Http headers.

Encryption manager
==================
The **EncryptionManager** service can be started directly from the **webtools** interface.

- In this case, a key is generated in the configuration file ``app/config/config.php``.

- The service is started in the ``services.php`` file.

.. code-block:: php
   :caption: app/config/services.php

    \Ubiquity\security\data\EncryptionManager::start($config);

.. note:: By default, encryption is performed in ``AES-128``.

.. image:: /_static/images/security/encryption-manager-started.png
   :class: bordered

Changing the cipher:
^^^^^^^^^^^^^^^^^^^^
Upgrade to AES-256:

.. code-block:: php
   :caption: app/config/services.php

   \Ubiquity\security\data\EncryptionManager::startProd($config, Encryption::AES256);

Generate a new key:

.. code-block:: bash

   Ubiquity new:key 256

The new key is generated in the ``app/config/config.php`` file.

Cookie encryption
-----------------
Cookies can be encrypted by default, by adding this in ``services.php``:

.. code-block:: php
   :caption: app/config/services.php

    use Ubiquity\utils\http\UCookie;
    use Ubiquity\contents\transformation\transformers\Crypt;

    UCookie::setTransformer(new Crypt());

.. image:: /_static/images/security/cookie-crypt.png
   :class: bordered

Model data encryption
---------------------
The ``Crypt`` transformer can also be used on the members of a model:

.. code-block:: php
   :caption: app/models/User.php

    class Foo{
        #[Transformer(name: "crypt")]
        private $secret;
        ...
    }

Usage:

.. code-block:: php

   $o=new Foo();
   $o->setSecret('bar');
   TransformersManager::transformInstance($o);// secret member is encrypted

Generic Data encryption
-----------------------
Strings encryption:

.. code-block:: php

    $encryptedBar=EncryptionManager::encryptString('bar');

To then decrypt it:

.. code-block:: php

    echo EncryptionManager::decryptString($encryptedBar);


It is possible to encrypt any type of data:

.. code-block:: php

    $encryptedUser=EncryptionManager::encrypt($user);

To then decrypt it, with possible serialisation/deserialisation if it is an object:

.. code-block:: php

    $user=EncryptionManager::decrypt($encryptedUser);


