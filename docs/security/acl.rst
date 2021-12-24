.. _aclModule:
ACL management
**************

.. |br| raw:: html

   <br />

Installation
============

Install the **Ubiquity-acl** module from the command prompt or from the **Webtools** (Composer part).

.. code-block:: bash

    composer require phpmv/ubiquity-acl

Then activate the display of the Acl part in the **Webtools**:

.. image:: /_static/images/security/display-acl.png
   :class: bordered

ACL interface in **webtools**:

.. image:: /_static/images/security/acl-part.png
   :class: bordered

Acl Rules
=========

ACLs are used to define access to an Ubiquity application. They are defined according to the following principles:

An Ubiquity application is composed of :
  * **Resources** (possibly controllers, or actions of these controllers)
  * **Roles**, possibly assigned to users. Each **Role** can inherit parent roles.
  * **Permissions**, which correspond to a right to do. Each permission has a level (represented by an integer value).

Additional rules:
  * An AclElement (**Allow**) grants Permission to a Role on a Resource.
  * Each role inherits authorisations from its parents, in addition to its own.
  * If a role has a certain level of access permission on a resource, it will also have all the permissions of a lower level on that resource.
  * The association of a resource and a permission to a controller or a controller action defines a **map** element.


.. image:: /_static/images/security/acl-diagram.png
   :class: bordered

Naming tips:
  * Role, in capital letters, beginning with an arobase (@USER, @ADMIN, @ALL...).
  * Permissions, in upper case, named using a verb (READ, WRITE, OPEN...).
  * Resource, capitalized on the first letter (Products, Customers...)




ACL Starting
============
The **AclManager** service can be started directly from the **webtools** interface, in the **Security** part.

- The service is started in the ``services.php`` file.

.. code-block:: php
   :caption: app/config/services.php

    \Ubiquity\security\acl\AclManager::startWithCacheProvider();

ACLCacheProvider
----------------
This default provider allows you to manage ACLs defined through attributes or annotations.

AclController
^^^^^^^^^^^^^

An AclController enables automatic access management based on ACLs to its own resources. |br|
It is possible to create them automatically from **webtools**.

.. image:: /_static/images/security/acls/new-acl-controller.png
   :class: bordered

But it is just a basic controller, using the AclControllerTrait feature.

This controller just goes to redefine the ``_getRole`` method, so that it returns the role of the active user, for example.

.. code-block:: php
   :caption: app/controllers/BaseAclController.php

   <?php
   namespace controllers;

   use Ubiquity\controllers\Controller;
   use Ubiquity\security\acl\controllers\AclControllerTrait;
   use Ubiquity\attributes\items\acl\Allow;

   class BaseAclController extends Controller {
   use AclControllerTrait;

      #[Allow('@ME')]
      public function index() {
         $this->loadView("BaseAclController/index.html");
      }

      public function _getRole() {
         $_GET['role']??'@ME';//Just for testing: logically, this is the active user's role
      }

      /**
       * {@inheritdoc}
       * @see \Ubiquity\controllers\Controller::onInvalidControl()
       */
      public function onInvalidControl() {
         echo $this->_getRole() . ' is not allowed!';
      }
   }

Authorisation has been granted for the resource:
  * Without specifying the resource, the controller's actions are defined as a resource.
  * Without specifying the permission, the ``ALL`` permission is used.

.. image:: /_static/images/security/acls/me-allow.png
   :class: bordered

And this association is present in the Acls map:

.. image:: /_static/images/security/acls/me-map.png
   :class: bordered


AclController with authentication
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. note::
   The use of both ``WithAuthTrait`` and ``AclControllerTrait`` requires to remove the ambiguity about the ``isValid`` method.

.. code-block:: php
   :caption: app/controllers/BaseAclController.php

   class BaseAclController extends Controller {
      use AclControllerTrait,WithAuthTrait{
         WithAuthTrait::isValid insteadof AclControllerTrait;
         AclControllerTrait::isValid as isValidAcl;
      }

      public function isValid($action){
           return parent::isValid($action)&& $this->isValidAcl($action);
      }
   }


Allow with Role, resource and permission
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Allow without prior creation:

``@USER`` is allowed to access to ``Foo`` resource with ``READ`` permission.

.. code-block:: php
   :caption: app/controllers/BaseAclController.php

   use Ubiquity\attributes\items\acl\Allow;

   class BaseAclController extends Controller {
   use AclControllerTrait;
      ...

      #[Allow('@USER','Foo', 'READ')]
      public function foo(){
         echo 'foo page allowed for @USER and @ME';
      }
   }

.. note::
   The role, resource and permission are automatically created as soon as they are invoked with ``Allow``.

Allow with explicit creation:

.. code-block:: php
   :caption: app/controllers/BaseAclController.php

   use Ubiquity\attributes\items\acl\Allow;
   use Ubiquity\attributes\items\acl\Permission;

   class BaseAclController extends Controller {
   use AclControllerTrait;
      ...

      #[Permission('READ',500)]
      #[Allow('@USER','Foo', 'READ')]
      public function foo(){
         echo 'foo page allowed for @USER and @ME';
      }
   }

Adding ACL at runtime
^^^^^^^^^^^^^^^^^^^^^

Whether in a controller or in a service, it is possible to add Roles, Resources, Permissions and Authorizations at runtime:

For example :\\
Adding a Role ``@USER`` inheriting from ``@GUEST``.

.. code-block:: php

   use Ubiquity\security\acl\AclManager;

   AclManager::addRole('@GUEST');
   AclManager::addRole('@USER',['@GUEST']);


Defining ACLs with Database
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The ACLs defined in the database are additional to the ACLs defined via annotations or attributes.

Initializing
------------

The initialization allows to create the tables associated to the ACLs (Role, Resource, Permission, AclElement). It needs to be done only once, and in dev mode only.

To place for example in ``app/config/bootstrap.php`` file:


.. code-block:: php

   use Ubiquity\controllers\Startup;
   use Ubiquity\security\acl\AclManager;

   $config=Startup::$config;
   AclManager::initializeDAOProvider($config, 'default');

Starting
--------
In ``app/config/services.php`` file :

.. code-block:: php

   use Ubiquity\security\acl\AclManager;
   use Ubiquity\security\acl\persistence\AclCacheProvider;
   use Ubiquity\security\acl\persistence\AclDAOProvider;
   use Ubiquity\orm\DAO;

   DAO::start();//Optional, to use only if dbOffset is not default

   AclManager::start();
   AclManager::initFromProviders([
       new AclCacheProvider(), new AclDAOProvider($config)
   ]);

Strategies for defining ACLs
============================

With few resources:
-------------------
Defining authorisations for each controller's action or action group:

Resources logically correspond to controllers, and permissions to actions.
But this rule may not be respected, and an action may be defined as a resource, as required.

The only mandatory rule is that a Controller/action pair can only correspond to one Resource/permission pair (not necessarily unique).


.. code-block:: php
   :caption: app/controllers/BaseAclController.php

   namespace controllers;

   use Ubiquity\controllers\Controller;
   use Ubiquity\security\acl\controllers\AclControllerTrait;
   use Ubiquity\attributes\items\acl\Permission;
   use Ubiquity\attributes\items\acl\Resource;

   #[Resource('Foo')]
   #[Allow('@ADMIN')]
   class FooController extends Controller {
      use AclControllerTrait;

      #[Allow('@NONE')]
      public function index() {
         echo 'index';
      }

      #[Allow('@USER')]
      public function read() {
         echo 'read';
      }

      #[Allow('@USER')]
      public function write() {
         echo 'write';
      }

      public function admin() {
         echo 'admin';
      }

      public function _getRole() {
         return $_GET['role']??'@NONE';
      }

      /**
       * {@inheritdoc}
       * @see \Ubiquity\controllers\Controller::onInvalidControl()
       */
      public function onInvalidControl() {
         echo $this->_getRole() . ' is not allowed!';
      }

   }



With more resources:
--------------------


.. code-block:: php
   :caption: app/controllers/BaseAclController.php

   namespace controllers;

   use Ubiquity\controllers\Controller;
   use Ubiquity\security\acl\controllers\AclControllerTrait;
   use Ubiquity\attributes\items\acl\Permission;
   use Ubiquity\attributes\items\acl\Resource;

   #[Resource('Foo')]
   class FooController extends Controller {
      use AclControllerTrait;

      #[Permission('INDEX',1)]
      public function index() {
         echo 'index';
      }

      #[Permission('READ',2)]
      public function read() {
         echo 'read';
      }

      #[Permission('WRITE',3)]
      public function write() {
         echo 'write';
      }

      #[Permission('ADMIN',10)]
      public function admin() {
         echo 'admin';
      }

      public function _getRole() {
         return $_GET['role']??'NONE';
      }

      /**
       * {@inheritdoc}
       * @see \Ubiquity\controllers\Controller::onInvalidControl()
       */
      public function onInvalidControl() {
         echo $this->_getRole() . ' is not allowed!';
      }

   }


