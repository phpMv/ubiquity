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
****************
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
         return '@ME';
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

.. image:: /_static/images/security/acls/me-allow.png
   :class: bordered

And this association is present in the Acls map:

.. image:: /_static/images/security/acls/me-map.png
   :class: bordered


