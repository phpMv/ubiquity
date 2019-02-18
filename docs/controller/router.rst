Router
======
.. |br| raw:: html

   <br />
Routing can be used in addition to the default mechanism that associates ``controller/action/{parameters}`` with an url. |br|
Routing works by using the **@route** annotation on controller methods.

Routes definition
-----------------
Simple route
^^^^^^^^^^^^



Creation
^^^^^^^
.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 7-9
   
   namespace controllers;
    /**
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   
   	/**
    	* @route("products")
    	*/
   	public function index(){}
   
   }

The method ``Products::index()`` will be accessible via the url ``/products``.

Route parameters
^^^^^^^^^^^^^^^^
A route can have parameters:

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 9-12
   
   namespace controllers;
    /**
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   	...
    	/**
    	* Matches products/*
    	*
    	* @route("products/{value}")
    	*/
    	public function search($value){
    		// $value will equal the dynamic part of the URL
    		// e.g. at /products/brocolis, then $value='brocolis'
    		// ...
    	}
   }
Route optional parameters
^^^^^^^^^^^^^^^^^^^^^^^
A route can define optional parameters, if the associated method has optional arguments:

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 9-12
   
   namespace controllers;
    /**
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   	...
    	/**
    	* Matches products/all/(.*?)/(.*?)
    	*
    	* @route("products/all/{pageNum}/{countPerPage}")
    	*/
    	public function list($pageNum,$countPerPage=50){
    		// ...
    	}
   }


Route requirements
^^^^^^^^^^^^^^^^^^

**php** being an untyped language, it is possible to add specifications on the variables passed in the url via the attribute **requirements**.

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 10
   
   namespace controllers;
    /**
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   	...
    	/**
    	* Matches products/all/(\d+)/(\d?)
    	*
    	* @route("products/all/{pageNum}/{countPerPage}","requirements"=>["pageNum"=>"\d+","countPerPage"=>"\d?"])
    	*/
    	public function list($pageNum,$countPerPage=50){
    		// ...
    	}
   }
   

The defined route matches these urls:
  - ``products/all/1/20``
  - ``products/all/5/`` 
but not with that one:
  - ``products/all/test``
  

Route http methods
^^^^^^^^^^^^^^^^^^

It is possible to specify the http method or methods associated with a route:

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 8
   
   namespace controllers;
    /**
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   
   	/**
    * @route("products","methods"=>["get"])
    */
   	public function index(){}
   
   }

The **methods** attribute can accept several methods: |br|
``@route("testMethods","methods"=>["get","post","delete"])``

It is also possible to use specific annotations **@get**, **@post**... |br|
``@get("products")``

Route name
^^^^^^^^^^
It is possible to specify the **name** of a route, this name then facilitates access to the associated url. |br|
If the **name** attribute is not specified, each route has a default name, based on the pattern **controllerName_methodName**.

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 7-9
   
   namespace controllers;
    /**
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   
   	/**
    	* @route("products","name"=>"products_index")
    	*/
   	public function index(){}
   
   }

URL or path generation
^^^^^^^^^^^^^^^^^^^^^^
Route names can be used to generate URLs or paths.

Linking to Pages in Twig

.. code-block:: html+twig
   
   <a href="{{ path('products_index') }}">Products</a>
   

Global route
^^^^^^^^^^^^
The **@route** annotation can be used on a controller class :

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 4
   
   namespace controllers;
    /**
    * @route("/product")
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   
    ...
   	/**
    * @route("/all")
    **/
   	public function display(){}
   
   }

In this case, the route defined on the controller is used as a prefix for all controller routes : |br|
The generated route for the action **display** is ``/product/all``

automated routes
~~~~~~~~~~~~~~~~

If a global route is defined, it is possible to add all controller actions as routes (using the global prefix), by setting the **automated** parameter :

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 3
   
   namespace controllers;
    /**
    * @route("/product","automated"=>true)
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   
   	public function generate(){}
   	
   	public function display(){}
   
   }
   

inherited routes
~~~~~~~~~~~~~~~~

With the **inherited** attribute, it is also possible to generate the declared routes in the base classes,
or to generate routes associated with base class actions if the **automated** attribute is set to true in the same time.

The base class:

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsBase.php
   
   namespace controllers;
    /**
    * Controller ProductsBase
    **/
   abstract class ProductsBase extends ControllerBase{
   
   	/**
   	*@route("(index/)?")
   	**/   	
   	public function index(){}

   	/**
   	*@route("sort/{name}")
   	**/   	
   	public function sortBy($name){}
   
   }
   
The derived class using inherited attribute:

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 3
   
   namespace controllers;
    /**
    * @route("/product","inherited"=>true)
    * Controller ProductsController
    **/
   class ProductsController extends ProductsBase{
   
   	public function display(){}
   	   
   }
   

The **inherited** attribute defines the 2 routes contained in **ProductsBase**:
  - `/products/(index/)?`
  - `/products/sort/{name}`


If the **automated** and **inherited** attributes are combined, the base class actions are also added to the routes.
