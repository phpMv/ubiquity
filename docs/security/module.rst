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

The installation of the Security module enables by default the CSRF protection of the session:

.. image:: /_static/images/security/security-part.png
   :class: bordered

Encryption manager
==================
The **EncryptionManager** service can be started directly from the **webtools** interface.

- In this case, a key is generated in the configuration file ``app/config/config.php``.

- The service is started in the ``services.php`` file.

.. code-block:: php
   :caption: app/config/services.php

    \Ubiquity\security\data\EncryptionManager::start($config);

.. note:: By default, encryption is performed in ``AES-256``.

.. image:: /_static/images/security/encryption-manager-started.png
   :class: bordered

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


Generic Data encryption
-----------------------
It is possible to encrypt any type of data:

.. code-block:: php

    $encryptedUser=EncryptionManager::encrypt($user);

To then decrypt it, with possible serialisation/deserialisation if it is an object:

.. code-block:: php

    $user=EncryptionManager::decrypt($encryptedUser);


