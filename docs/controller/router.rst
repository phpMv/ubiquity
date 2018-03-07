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
   :caption: app/controllers/Products.php
   :emphasize-lines: 7-9
   
   namespace controllers;
    /**
    * Controller Products
    **/
   class Products extends ControllerBase{
   
   	/**
    	* @route("products")
    	*/
   	public function index(){}
   
   }

The method ``Products::index()`` will be accessible via the url ``/products``.

Route parameters
^^^^^^^^^^^^^^^
A route can have parameters:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Products.php
   :emphasize-lines: 9-12
   
   namespace controllers;
    /**
    * Controller Products
    **/
   class Products extends ControllerBase{
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
   :caption: app/controllers/Products.php
   :emphasize-lines: 9-12
   
   namespace controllers;
    /**
    * Controller Products
    **/
   class Products extends ControllerBase{
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
   :caption: app/controllers/Products.php
   :emphasize-lines: 7-9
   
   namespace controllers;
    /**
    * Controller Products
    **/
   class Products extends ControllerBase{
   
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

