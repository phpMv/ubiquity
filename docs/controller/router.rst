Router
======
.. |br| raw:: html

   <br />
Routing can be used in addition to the default mechanism that associates ``controller/action/{parameters}`` with an url. |br|
Routing works by using the **@route** annotation on controller methods.

Routes definition
-------------------
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
    	* @Route("products/{value}")
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
    	* @Route("products/all/{pageNum}/{countPerPage}")
    	*/
    	public function list($pageNum,$countPerPage=50){
    		// ...
    	}
   }

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
^^^^^^^^^^^^^^^^^^^^
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

In this case, the route defined on the controller is used as a prefix for all controller routes :
The generated route for the action **display** is ``/product/all``

automated routes
~~~~~~~~~~~~~~~~
If a global route is defined, it is possible to add all controller actions as routes (using the global prefix),
 by setting the **automated** parameter :

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 4
   
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
   :emphasize-lines: 4
   
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
   :emphasize-lines: 4
   
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

