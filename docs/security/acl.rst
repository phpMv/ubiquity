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

Acl management
==============

ACLs are used to define access to an Ubiquity application. They are defined according to the following principles:

An Ubiquity application is composed of :
  * **Resources** (possibly controllers, or actions of these controllers)
  * **Roles**, possibly assigned to users. Each **Role** can inherit parent roles.
  * **Permissions**, which correspond to a right to do. Each permission has a level (represented by an integer value).


  * An AclElement (**Allow**) grants Permission to a Role on a Resource.
  * Each role inherits authorisations from its parents, in addition to its own.
  * If a role has a certain level of access permission on a resource, it will also have all the permissions of a lower level on that resource.


