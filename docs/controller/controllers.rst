Ubiquity Controllers
====================
.. |br| raw:: html

   <br />
A controller is a PHP class inheriting from ``micro\controllers\Controller``, providing an entry point in the application. |br| 
Controllers and their methods define accessible URLs.

Controller creation
-------------------
The easiest way to create a controller is to do it from the devtools.

From the command prompt, go to the project folder. |br| 
To create the Products controller, use the command:

.. code-block:: bash
   
   Micro controller Products

The Products.php controller is created in the ``app/controllers`` folder of the project.

.. code-block:: php
   :linenos:
   :caption: app/controllers/Products.php
   
   namespace controllers;
    /**
    * Controller Products
    **/
   class Products extends ControllerBase{
   
   	public function index(){}
   
   }
It is now possible to access URLs (the ``index`` method is solicited by default):
::
    example.com/Products
    example.com/Products/index

.. note:: A controller can be created manually. In this case, he must respect the following rules:
          
          * The class must be in the **app/controllers** folder
          * The name of the class must match the name of the php file
          * It must inherit from **ControllerBase** and be defined in the namespace **controllers**
          * It must override the abstract **index** method

Methods
-------
public
^^^^^^
The second segment of the URI determines which public method in the controller gets called. |br| 
The “index” method is always loaded by default if the second segment of the URI is empty.

.. code-block:: php
   :linenos:
   :caption: app/controllers/First.php
   
   namespace controllers;
   class First extends ControllerBase{
   
   	public function hello(){
   		echo "Hello world!";
   	}
   
   }
The ``hello`` method of the ``First`` controller makes the following URL available:
::
    example.com/First/hello

method arguments
^^^^^^^^^^^^^^^^
the arguments of a method must be passed in the url, except if they are optional.

.. code-block:: php
   :caption: app/controllers/First.php
   
   namespace controllers;
   class First extends ControllerBase{
   
   	public function says($what,$who="world"){
   		echo $what." ".$who;
   	}
   
   }
The ``hello`` method of the ``First`` controller makes the following URLs available:
::
    example.com/First/says/hello (says hello world)
    example.com/First/says/Hi/everyone (says Hi everyone)
private
^^^^^^^
Private or protected methods are not accessible from the URL.

Default controller
------------------
The default controller can be set with the Router, in the ``services.php`` file
.. code-block:: php
   :caption: app/config/services.php
   
   Router::start();
   Router::addRoute("_default", "controllers\says\hello");

In this case, access to the ``example.com/`` URL calls the controller **First**, calls the **say** method and passes **hello** as parameter.
views loading
-------------
loading
^^^^^^^

view parameters
^^^^^^^^^^^^^^^

view result as string
^^^^^^^^^^^^^^^^^^^^^

view engine
^^^^^^^^^^^

initialize and finalize
-----------------------

Access control
--------------

Dependency injection
--------------------

namespaces
----------

Super class
-----------
