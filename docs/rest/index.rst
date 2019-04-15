Rest
====

The REST module implements a basic CRUD, |br|
with an authentication system, directly testable in the administration part.

REST and routing
----------------
The router is essential to the REST module, since REST (Respresentation State Transfer) is based on URLs and HTTP methods.

.. note::
   For performance reasons, REST routes are cached independently of other routes. |br|
   It is therefore necessary to start the router in a particular way to activate the REST routes and not to obtain a recurring 404 error.

The router is started in ``services.php``.

Without activation of REST routes:

.. code-block:: php
   :caption: app/config/services.php
   
   ...
   Router::start();

To enable REST routes in an application that also has a non-REST part:

.. code-block:: php
   :caption: app/config/services.php
   
   ...
   Router::startAll();

To activate only Rest routes:

.. code-block:: php
   
   Router::startRest();
   

It is possible to start routing conditionally (this method will only be more efficient if the number of routes is large in either part):

.. code-block:: php
   :caption: app/config/services.php
   
   ...
	if($config['isRest']()){
		Router::startRest();
	}else{
		Router::start();
	}

Resource REST
-------------

A REST controller can be directly associated with a resource (a model).

Creation
++++++++

With devtools:

.. code-block:: bash
   
   Ubiquity rest RestUsersController -r=User -p=/rest/users

Or with webtools:

Go to the **REST** section and choose **Add a new resource**:

.. image:: /_static/images/rest/addNewResource.png
   :class: bordered

The created controller :

.. code-block:: php
   :linenos:
   :caption: app/controllers/RestUsersController.php
   
	namespace controllers;
	
	/**
	 * Rest Controller RestUsersController
	 * @route("/rest/users","inherited"=>true,"automated"=>true)
	 * @rest("resource"=>"models\\User")
	 */
	class RestUsersController extends \Ubiquity\controllers\rest\RestController {
	
	}

Since the attributes **automated** and **inherited** of the route are set to true, the controller has the default routes of the parent class.

.. note
   The base controller RestController is not standardized, it should be considered as an example for data interrogation.

Test interface
++++++++++++++

Webtools provide an interface for querying datas:

.. image:: /_static/images/rest/createdResource.png
   :class: bordered

Getting one User
~~~~~~~~~~~~~~~~

A user instance can be accessed by its primary key (**id**):

.. image:: /_static/images/rest/getOneResource.png
   :class: bordered

Inclusion of associated members: the organization of the user

.. image:: /_static/images/rest/getOneResourceInclude.png
   :class: bordered

Inclusion of associated members: organization, connections and groups of the user

.. image:: /_static/images/rest/getOneResourceIncludeAll.png
   :class: bordered

Getting multiple users
~~~~~~~~~~~~~~~~~~~~~~

Getting all instances:

.. image:: /_static/images/rest/getAllOrgas.png
   :class: bordered

Setting a condition:

.. image:: /_static/images/rest/condition-orgas.png
   :class: bordered

Including associated members:

.. image:: /_static/images/rest/include-orgas.png
   :class: bordered

Adding a user
~~~~~~~~~~~~~

The datas are sent by the **POST** method, with a content type defined at ``application/x-www-form-urlencoded``:

Add name and domain parameters:

.. image:: /_static/images/rest/post-parameters.png
   :class: bordered

The addition requires an authentication, so an error is generated, with the status 401:

.. image:: /_static/images/rest/unauthorized-post.png
   :class: bordered

The administration interface allows you to simulate the default authentication and obtain a token, by requesting the **connect** method:

.. image:: /_static/images/rest/connect.png
   :class: bordered

The token is then automatically sent in the following requests. |br|
The record can then be inserted.

.. image:: /_static/images/rest/added.png
   :class: bordered


.. |br| raw:: html

   <br />
