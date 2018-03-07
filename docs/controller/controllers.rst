Controllers
==========
.. |br| raw:: html

   <br />
A controller is a PHP class inheriting from ``Ubiquity\controllers\Controller``, providing an entry point in the application. |br| 
Controllers and their methods define accessible URLs.

Controller creation
-------------------
The easiest way to create a controller is to do it from the devtools.

From the command prompt, go to the project folder. |br| 
To create the Products controller, use the command:

.. code-block:: bash
   
   Ubiquity controller Products

The ``Products.php`` controller is created in the ``app/controllers`` folder of the project.

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
          * The class must inherit from **ControllerBase** and be defined in the namespace **controllers**
          * and must override the abstract **index** method

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
   Router::addRoute("_default", "controllers\First");

In this case, access to the ``example.com/`` URL loads the controller **First** and calls the default **index** method.

views loading
-------------
loading
^^^^^^^
Views are stored in the ``app/views`` folder. They are loaded from controller methods. |br| 
By default, it is possible to create views in php, or with twig. |br|
`Twig <https://twig.symfony.com>`_ is the default template engine for html files.

php view loading
~~~~~~~~~~~~~~~~
If the file extension is not specified, the **loadView** method loads a php file.

.. code-block:: php
   :caption: app/controllers/First.php
   
   namespace controllers;
   class First extends ControllerBase{
   	public function displayPHP(){
   		//loads the view app/views/index.php
   		$this->loadView("index");
   	}
   }

twig view loading
~~~~~~~~~~~~~~~~
If the file extension is html, the **loadView** method loads an html twig file.

.. code-block:: php
   :caption: app/controllers/First.php
   
   namespace controllers;
   class First extends ControllerBase{
   	public function displayTwig(){
   		//loads the view app/views/index.html
   		$this->loadView("index.html");
   	}
   }

view parameters
^^^^^^^^^^^^^^^
One of the missions of the controller is to pass variables to the view. |br| 
This can be done at the loading of the view, with an associative array:

.. code-block:: php
   :caption: app/controllers/First.php
   
   class First extends ControllerBase{
   	public function displayTwigWithVar($name){
   		$message="hello";
   		//loads the view app/views/index.html
   		$this->loadView("index.html",["recipient"=>$name,"message"=>$message]);
   	}
   }

The keys of the associative array create variables of the same name in the view. |br| 
Using of this variables in Twig:

.. code-block:: html
   :caption: app/views/index.html
   
   <h1>{{message}} {{recipient}}</h1>

Variables can also be passed before the view is loaded:

.. code-block:: php
   
   //passing one variable
   $this->view->setVar("title"=>"Message");
   //passing an array of 2 variables
   $this->view->setVars(["message"=>$message,"recipient"=>$name]);
   //loading the view that now contains 3 variables
   $this->loadView("First/index.html");

view result as string
^^^^^^^^^^^^^^^^^^^^^
It is possible to load a view, and to return the result in a string, assigning true to the 3rd parameter of the loadview method :

.. code-block:: php
   
   $viewResult=$this->loadView("First/index.html",[],true);
   echo $viewResult;

multiple views loading
^^^^^^^^^^^^^^^^^^^^^^
A controller can load multiple views:

.. code-block:: php
   :caption: app/controllers/Products.php
   
   namespace controllers;
   class Products extends ControllerBase{
   	public function all(){
   		$this->loadView("Main/header.html",["title"=>"Products"]);
   		$this->loadView("Products/index.html",["products"=>$this->products]);
   		$this->loadView("Main/footer.html");
   	}
   }

.. important:: A view is often partial. It is therefore important not to systematically integrate the **html** and **body** tags defining a complete html page.

views organization
^^^^^^^^^^^^^^^^^^
It is advisable to organize the views into folders. The most recommended method is to create a folder per controller, and store the associated views there. |br| 
To load the ``index.html`` view, stored in ``app/views/First``:

.. code-block:: php
   
   $this->loadView("First/index.html");

initialize and finalize
-----------------------

Access control
--------------

Forwarding
----------

Dependency injection
--------------------

namespaces
----------

Super class
-----------
