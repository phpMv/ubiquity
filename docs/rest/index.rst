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


.. image:: /_static/images/rest/getOneResource.png
   :class: bordered


.. |br| raw:: html

   <br />
