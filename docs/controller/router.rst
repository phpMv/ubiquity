Router
======

Routing can be used in addition to the default mechanism that associates ``controller/action/{parameters}`` with an url. |br|

Dynamic routes
--------------
Dynamic routes are defined at runtime. |br|
It is possible to define these routes in the **app/config/services.php** file.

.. important::
	Dynamic routes should only be used if the situation requires it:

	- in the case of a micro-application
	- if a route must be dynamically defined
	
	In all other cases, it is advisable to declare the routes with annotations, to benefit from caching.

Callback routes
^^^^^^^^^^^^^^^
The most basic Ubiquity routes accept a Closure. |br|
In the context of micro-applications, this method avoids having to create a controller.

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   :emphasize-lines: 3-5
   
	use Ubiquity\controllers\Router;
	
	Router::get("foo", function(){
		echo 'Hello world!';
	});


Callback routes can be defined for all http methods with:

- Router::post
- Router::put
- Router::delete
- Router::patch
- Router::options

Controller routes
^^^^^^^^^^^^^^^^^
Routes can also be associated more conventionally with an action of a controller:

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   :emphasize-lines: 3
   
	use Ubiquity\controllers\Router;
	
	Router::addRoute("bar", \controllers\FooController::class,'index');

The method ``FooController::index()`` will be accessible via the url ``/bar``.

In this case, the **FooController** must be a class inheriting from **Ubiquity\controllers\Controller** or one of its subclasses,
and must have an **index** method:

.. code-block:: php
   :linenos:
   :caption: app/controllers/FooController.php
   :emphasize-lines: 5-7

	namespace controllers;
	
	class FooController extends ControllerBase{
	
		public function index(){
			echo 'Hello from foo';
		}
	}

Default route
^^^^^^^^^^^^^
The default route matches the path **/**. |br|
It can be defined using the reserved path **_default**

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   :emphasize-lines: 3
   
	use Ubiquity\controllers\Router;
	
	Router::addRoute("_default", \controllers\FooController::class,'bar');


Static routes
-------------

Static routes are defined using the **@route** annotation on controller methods.

.. note::
	These annotations are never read at runtime. |br|
	It is necessary to reset the router cache to take into account the changes made on the routes.

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
^^^^^^^^^^^^^^^^^^^^^^^^^
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

Route priority
^^^^^^^^^^^^^^
The prority parameter of a route allows this route to be resolved more quickly.

The higher the priority parameter, the more the route will be defined at the beginning of the stack of routes in the cache.

In the example below, the **products/all** route will be defined before the **/products** route.

.. code-block:: php
   :linenos:
   :caption: app/controllers/ProductsController.php
   :emphasize-lines: 8,13
   
   namespace controllers;
    /**
    * Controller ProductsController
    **/
   class ProductsController extends ControllerBase{
   
   	/**
    * @route("products","priority"=>1)
    */
   	public function index(){}
   	
   	/**
    * @route("products/all","priority"=>10)
    */
   	public function all(){}
   
   }

Routes response caching
-----------------------
It is possible to cache the response produced by a route:

In this case, the response is cached and is no longer dynamic.

.. code-block:: php
   
	/**
	* @route("products/all","cache"=>true)
	*/
	public function all(){}

Cache duration
^^^^^^^^^^^^^^
The **duration** is expressed in seconds, if it is omitted, the duration of the cache is infinite.

.. code-block:: php
	
   	/**
    * @route("products/all","cache"=>true,"duration"=>3600)
    */
   	public function all(){}
   	

Cache expiration
^^^^^^^^^^^^^^^^
It is possible to force reloading of the response by deleting the associated cache.

.. code-block:: php

		Router::setExpired("products/all");

Dynamic routes caching
----------------------

Dynamic routes can also be cached.

.. important::
   This possiblity is only useful if this caching is not done in production, but at the time of initialization of the cache.

.. code-block:: php

	Router::get("foo", function(){
		echo 'Hello world!';
	});
	
	Router::addRoute("string", \controllers\Main::class,"index");
	CacheManager::storeDynamicRoutes(false);

Checking routes with devtools :

.. code-block:: bash

   Ubiquity info:routes
   
.. image:: /_static/images/quick-start/ubi-version.png
   :class: console

.. |br| raw:: html

   <br />