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
	
	Router::addRoute('bar', \controllers\FooController::class,'index');

The method ``FooController::index()`` will be accessible via the url ``/bar``.

In this case, the **FooController** must be a class inheriting from **Ubiquity\\controllers\\Controller** or one of its subclasses,
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

Static routes are defined using annotation or with php native attributes since ``Ubiquity 2.4.0``.

.. note::
	These annotations or attributes are never read at runtime. |br|
	It is necessary to reset the router cache to take into account the changes made on the routes.

Creation
^^^^^^^

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 7

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class ProductsController extends ControllerBase{

             #[Route('products')]
             public function index(){}

         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 5-7

         namespace controllers;

         class ProductsController extends ControllerBase{

             /**
              * @route("products")
              */
             public function index(){}

         }


The method ``Products::index()`` will be accessible via the url ``/products``.


.. note::
   The initial or terminal slash is ignored in the path. The following routes are therefore equivalent: |br|
     * ``#[Route('products')]``
     * ``#[Route('/products')]``
     * ``#[Route('/products/')]``

Route parameters
^^^^^^^^^^^^^^^^
A route can have parameters:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 7

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class ProductsController extends ControllerBase{
         ...
              #[Route('products/{value}')]
              public function search($value){
                  // $value will equal the dynamic part of the URL
                  // e.g. at /products/brocolis, then $value='brocolis'
                  // ...
              }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 6

         namespace controllers;

         class ProductsController extends ControllerBase{
         ...
             /**
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

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 7

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class ProductsController extends ControllerBase{
            ...
            #[Route('products/all/{pageNum}/{countPerPage}')]
            public function list($pageNum,$countPerPage=50){
               // ...
            }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 6

         namespace controllers;

         class ProductsController extends ControllerBase{
            ...
            /**
             * @route("products/all/{pageNum}/{countPerPage}")
             */
            public function list($pageNum,$countPerPage=50){
               // ...
            }
         }

Route requirements
^^^^^^^^^^^^^^^^^^

It is possible to add specifications on the variables passed in the url via the attribute **requirements**.

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 7

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class ProductsController extends ControllerBase{
            ...
            #[Route('products/all/{pageNum}/{countPerPage}',requirements: ["pageNum"=>"\d+","countPerPage"=>"\d?"])]
            public function list($pageNum,$countPerPage=50){
               // ...
            }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 6

         namespace controllers;

         class ProductsController extends ControllerBase{
            ...
            /**
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
  

Parameter typing
^^^^^^^^^^^^^^^^
The route declaration takes into account the data types passed to the action, which avoids adding requirements for simple types (int, bool, float).

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 7

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class ProductsController extends ControllerBase{
            ...
            #[Route('products/{productNumber}')]
            public function one(int $productNumber){
               // ...
            }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 6

         namespace controllers;

         class ProductsController extends ControllerBase{
            ...
            /**
             * @route("products/{productNumber}")
             */
            public function one(int $productNumber){
               // ...
            }
         }

The defined route matches these urls:
  - ``products/1``
  - ``products/20`` 
but not with that one:
  - ``products/test``
  

Correct values by data type:
  - ``int``: ``1``...
  - ``bool``: ``0`` or ``1``
  - ``float``: ``1`` ``1.0`` ...

Route http methods
^^^^^^^^^^^^^^^^^^

It is possible to specify the http method or methods associated with a route:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 7

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class ProductsController extends ControllerBase{

            #[Route('products',methods: ['get','post'])]
            public function index(){}

         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 6

         namespace controllers;

         class ProductsController extends ControllerBase{

            /**
             * @route("products","methods"=>["get","post"])
             */
            public function index(){}

         }

The **methods** attribute can accept several methods: |br|
``@route("testMethods","methods"=>["get","post","delete"])`` |br|
``#[Route('testMethods', methods: ['get','post','delete'])]``

The **@route** annotation or **Route** attribute defaults to all HTTP methods. |br|
There is a specific annotation for each of the existing HTTP methods:
 - **@get** => **Get**
 - **@post** => **Post**
 - **@put** => **Put**
 - **@patch** => **Patch**
 - **@delete** => **Delete**
 - **@head** => **Head**
 - **@options** => **Options**

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 7

         namespace controllers;

         use Ubiquity\attributes\items\router\Get;

         class ProductsController extends ControllerBase{

            #[Get('products')]
            public function index(){}

         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/ProductsController.php
         :emphasize-lines: 6

         namespace controllers;

         class ProductsController extends ControllerBase{

            /**
             * @get("products")
             */
            public function index(){}

         }


Route name
^^^^^^^^^^
It is possible to specify the **name** of a route, this name then facilitates access to the associated url. |br|
If the **name** attribute is not specified, each route has a default name, based on the pattern **controllerName_methodName**.

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 7

           namespace controllers;

           use Ubiquity\attributes\items\router\Route;

           class ProductsController extends ControllerBase{

              #[Route('products',name: 'products.index')]
              public function index(){}

           }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 5-7

           namespace controllers;

           class ProductsController extends ControllerBase{

              /**
               * @route("products","name"=>"products.index")
               */
              public function index(){}

           }

URL or path generation
^^^^^^^^^^^^^^^^^^^^^^
Route names can be used to generate URLs or paths.

Linking to Pages in Twig

.. code-block:: html+twig
   
   <a href="{{ path('products.index') }}">Products</a>
   

Global route
^^^^^^^^^^^^
The **@route** annotation can be used on a controller class :

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 5

           namespace controllers;

           use Ubiquity\attributes\items\router\Route;

           #[Route('products')]
           class ProductsController extends ControllerBase{
              ...
              #[Route('/all')]
              public function display(){}

           }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 3

           namespace controllers;
           /**
            * @route("/product")
            */
           class ProductsController extends ControllerBase{

              ...
              /**
               * @route("/all")
               */
              public function display(){}

           }

In this case, the route defined on the controller is used as a prefix for all controller routes : |br|
The generated route for the action **display** is ``/product/all``

automated routes
~~~~~~~~~~~~~~~~

If a global route is defined, it is possible to add all controller actions as routes (using the global prefix), by setting the **automated** parameter :

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 5

           namespace controllers;

           use Ubiquity\attributes\items\router\Route;

           #[Route('/products',automated: true)]
           class ProductsController extends ControllerBase{

              public function index(){}

              public function generate(){}

              public function display($id){}

           }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 3

           namespace controllers;
           /**
            * @route("/product","automated"=>true)
            */
           class ProductsController extends ControllerBase{

              public function index(){}

              public function generate(){}

              public function display($id){}

           }

The **automated** attribute defines the 3 routes contained in **ProductsController**:
  - `/product/(index/)?`
  - `/product/generate`
  - `/product/display/{id}`

inherited routes
~~~~~~~~~~~~~~~~

With the **inherited** attribute, it is also possible to generate the declared routes in the base classes,
or to generate routes associated with base class actions if the **automated** attribute is set to true in the same time.

The base class:

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsBase.php

            namespace controllers;

            use Ubiquity\attributes\items\router\Route;

            abstract class ProductsBase extends ControllerBase{

                #[Route('(index/)?')]
                public function index(){}

                #[Route('sort/{name}')]
                public function sortBy($name){}

            }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsBase.php

           namespace controllers;

           abstract class ProductsBase extends ControllerBase{

              /**
               *@route("(index/)?")
               */
              public function index(){}

              /**
               * @route("sort/{name}")
               */
              public function sortBy($name){}

           }

The derived class using inherited members:

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 5

           namespace controllers;

           use Ubiquity\attributes\items\router\Route;

           #[Route('/product',inherited: true)]
           class ProductsController extends ProductsBase{

              public function display(){}

           }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 3

           namespace controllers;
           /**
            * @route("/product","inherited"=>true)
            */
           class ProductsController extends ProductsBase{

              public function display(){}

           }

The **inherited** attribute defines the 2 routes defined in **ProductsBase**:
  - `/products/(index/)?`
  - `/products/sort/{name}`


If the **automated** and **inherited** attributes are combined, the base class actions are also added to the routes.

Global route parameters
~~~~~~~~~~~~~~~~~~~~~~~
The global part of a route can define parameters, which will be passed in all generated routes. |br|
These parameters can be retrieved through a public data member:

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/FooController.php
           :emphasize-lines: 5

           namespace controllers;

           use Ubiquity\attributes\items\router\Route;

           #[Route('/foo/{bar}',automated: true)]
           class FooController {

              public string $bar;

              public function display(){
                  echo $this->bar;
              }

           }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/FooController.php
           :emphasize-lines: 4

           namespace controllers;

           /**
            * @route("/foo/{bar}","automated"=>true)
            */
           class FooController {

              public string $bar;

              public function display(){
                  echo $this->bar;
              }

           }

Accessing the url ``/foo/bar/display`` displays the contents of the bar member.

Route without global prefix
~~~~~~~~~~~~~~~~~~~~~~~~~~~
If the global route is defined on a controller, all the generated routes in this controller are preceded by the prefix. |br|
It is possible to explicitly introduce exceptions on some routes, using the ``#/`` prefix.

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/FooController.php
           :emphasize-lines: 8

           namespace controllers;

           use Ubiquity\attributes\items\router\Route;

           #[Route('/foo',automated: true)]
           class FooController {

              #[Route('#/noRoot')]
              public function noRoot(){}

           }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/FooController.php
           :emphasize-lines: 9

           namespace controllers;

           /**
            * @route("/foo","automated"=>true)
            */
           class FooController {

             /**
              * @route("#/noRoot")
              */
              public function noRoot(){}

           }

The controller defines the ``/noRoot`` url, which is not prefixed with the ``/foo`` part.

Route priority
^^^^^^^^^^^^^^
The **prority** parameter of a route allows this route to be resolved in a priority order.

The higher the priority parameter, the more the route will be defined at the beginning of the stack of routes in the cache.

In the example below, the **products/all** route will be defined before the **/products** route.

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 7,10

           namespace controllers;

           use Ubiquity\attributes\items\router\Route;

           class ProductsController extends ControllerBase{

              #[Route('products', priority: 1)]
              public function index(){}

              #[Route('products/all', priority: 10)]
              public function all(){}

           }

   .. tab:: Annotations

        .. code-block:: php
           :linenos:
           :caption: app/controllers/ProductsController.php
           :emphasize-lines: 6,11

           namespace controllers;

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


The default priority value is ``0``.

Routes response caching
-----------------------
It is possible to cache the response produced by a route:

In this case, the response is cached and is no longer dynamic.

.. tabs::

   .. tab:: Attributes

        .. code-block:: php

           #[Route('products/all', cache: true)]
           public function all(){}

   .. tab:: Annotations

        .. code-block:: php

           /**
            * @route("products/all","cache"=>true)
            */
           public function all(){}


Cache duration
^^^^^^^^^^^^^^
The **duration** is expressed in seconds, if it is omitted, the duration of the cache is infinite.

.. tabs::

   .. tab:: Attributes

        .. code-block:: php

           #[Route('products/all', cache: true, duration: 3600)]
           public function all(){}

   .. tab:: Annotations

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
   This possibility is only useful if this caching is not done in production, but at the time of initialization of the cache.

.. code-block:: php

   Router::get("foo", function(){
      echo 'Hello world!';
   });

   Router::addRoute("string", \controllers\Main::class,"index");
   CacheManager::storeDynamicRoutes(false);

Checking routes with devtools :

.. code-block:: bash

   Ubiquity info:routes
   
.. image:: /_static/images/controllers/info-routes.png
   :class: console

Error management (404 & 500 errors)
-----------------------------------

Default routing system
^^^^^^^^^^^^^^^^^^^^^^

With the default routing system (the controller+action couple defining a route), the error handler can be redefined to customize the error management.

In the configuration file **app/config/config.php**, add the **onError** key, associated to a callback defining the error messages:

.. code-block:: php
   
   "onError"=>function ($code, $message = null,$controller=null){
      switch($code){
         case 404:
            $init=($controller==null);
            \Ubiquity\controllers\Startup::forward('IndexController/p404',$init,$init);
            break;
      }
   }

Implement the requested action **p404** in the **IndexController**:

.. code-block:: php
   :caption: app/controllers/IndexController.php
   
   ...
   
   public function p404(){
      echo "<div class='ui error message'><div class='header'>404</div>The page you are looking for doesn't exist!</div>";
   }

Routage with annotations
^^^^^^^^^^^^^^^^^^^^^^^^

It is enough in this case to add a last route disabling the default routing system, and corresponding to the management of the 404 error:

.. tabs::

   .. tab:: Attributes

        .. code-block:: php
           :caption: app/controllers/IndexController.php

           ...

           #[Route('{url}', priority: -1000)]
           public function p404($url){
              echo "<div class='ui error message'><div class='header'>404</div>The page `$url` you are looking for doesn't exist!</div>";
           }

   .. tab:: Annotations

        .. code-block:: php
           :caption: app/controllers/IndexController.php

           ...

           /**
            * @route("{url}","priority"=>-1000)
            */
           public function p404($url){
              echo "<div class='ui error message'><div class='header'>404</div>The page `$url` you are looking for doesn't exist!</div>";
           }

.. |br| raw:: html

   <br />