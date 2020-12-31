Quick start with console
========================

.. note:: If you do not like console mode, you can switch to quick-start with :ref:`web tools (UbiquityMyAdmin)<quickstart-html>`.

Install Composer
----------------
**ubiquity** utilizes Composer to manage its dependencies. So, before using, you will need to make sure you have `Composer <http://getcomposer.org/>`_ installed on your machine.

Install Ubiquity-devtools
-------------------------
Download the Ubiquity-devtools installer using Composer.

.. code-block:: bash
   
   composer global require phpmv/ubiquity-devtools
   
Test your recent installation by doing:

.. code-block:: bash
   
   Ubiquity version
   
.. image:: /_static/images/quick-start/ubi-version.png
   :class: console

You can get at all times help with a command by typing: ``Ubiquity help`` followed by what you are looking for.

Example :

.. code-block:: bash
   
   Ubiquity help project
   
   
Project creation
----------------
Create the **quick-start** projet

.. code-block:: bash
   
   Ubiquity new quick-start

Directory structure
-------------------
The project created in the **quick-start** folder has a simple and readable structure:

the **app** folder contains the code of your future application:
  
.. code-block:: bash
   
   app
    ├ cache
    ├ config
    ├ controllers
    ├ models
    └ views
   
Start-up
--------
Go to the newly created folder **quick-start** and start the build-in php server:

.. code-block:: bash
   
   Ubiquity serve
   
Check the correct operation at the address **http://127.0.0.1:8090**:

.. image:: /_static/images/quick-start/quick-start-main.png

.. note:: If port 8090 is busy, you can start the server on another port using -p option.

.. code-block:: bash
   
   Ubiquity serve -p=8095
   

Controller
----------

The console application **dev-tools** saves time in repetitive operations.
We go through it to create a controller.

.. code-block:: bash
   
   Ubiquity controller DefaultController
   
.. image:: /_static/images/quick-start/controller-creation.png
   :class: console

We can then edit ``app/controllers/DefaultController`` file in our favorite IDE:

.. code-block:: php
   :linenos:
   :caption: app/controllers/DefaultController.php
      
   namespace controllers;
    /**
     * Controller DefaultController
     */
   class DefaultController extends ControllerBase{
    	public function index(){}
   }

Add the traditional message, and test your page at ``http://127.0.0.1:8090/DefaultController``

.. code-block:: php
   :caption: app/controllers/DefaultController.php
   
   class DefaultController extends ControllerBase{

       public function index(){
           echo 'Hello world!';
       }

   }

For now, we have not defined routes, |br|
Access to the application is thus made according to the following scheme: |br|
``controllerName/actionName/param``

The default action is the **index** method, we do not need to specify it in the url.

Route
-----

.. important::
	The routing is defined with the attribute ``Route`` (with php>8) or the annotation ``@route`` and is not done in a configuration file: |br|
	it's a design choice.
	
The **automated** parameter set to **true** allows the methods of our class to be defined as sub routes of the main route ``/hello``.

With annotations:

.. code-block:: php
   :linenos:
   :caption: app/controllers/DefaultController.php
      
   namespace controllers;
   /**
    * Controller DefaultController
    * @route("/hello","automated"=>true)
    */
   class DefaultController extends ControllerBase{

       public function index(){
           echo 'Hello world!';
       }

   }

With attributes (php>8):

.. code-block:: php
   :linenos:
   :caption: app/controllers/DefaultController.php

   namespace controllers;
   use Ubiquity\attributes\items\router\Route;

   #[Route('/hello', automated: true)]
   class DefaultController extends ControllerBase{

       public function index(){
           echo 'Hello world!';
       }

   }

Router cache
^^^^^^^^^^^^
.. important::
	No changes on the routes are effective without initializing the cache. |br|
	Annotations are never read at runtime. This is also a design choice.

We can use the console for the cache re-initialization:

.. code-block:: bash
   
   Ubiquity init-cache

.. image:: /_static/images/quick-start/init-cache.png
   :class: console

Let's check that the route exists:

.. code-block:: bash
   
   Ubiquity info:routes

.. image:: /_static/images/quick-start/info-routes.png
   :class: console

We can now test the page at ``http://127.0.0.1:8090/hello``

Action & route with parameters
------------------------------

We will now create an action (sayHello) with a parameter (name), and the associated route (to): |br|
The route will use the parameter **name** of the action:

.. code-block:: bash

	Ubiquity action DefaultController.sayHello -p=name -r=to/{name}/
	
.. image:: /_static/images/quick-start/action-creation.png
   :class: console

After re-initializing the cache (**init-cache** command), the **info:routes** command should display:

.. image:: /_static/images/quick-start/2-routes.png
   :class: console

Change the code in your IDE: the action must say Hello to somebody...

.. code-block:: php
   :caption: app/controllers/DefaultController.php
   
	/**
	 * @route("to/{name}/")
	 */
	public function sayHello($name){
		echo 'Hello '.$name.'!';
	}

and test the page at ``http://127.0.0.1:8090/hello/to/Mr SMITH``

Action, route parameters & view
-------------------------------

We will now create an action (information) with two parameters (title and message), the associated route (info), and a view to display the message: |br|
The route will use the two parameters of the action.

.. code-block:: bash

	Ubiquity action DefaultController.information -p=title,message='nothing' -r=info/{title}/{message} -v
	

.. note:: The -v (--view) parameter is used to create the view associated with the action.

After re-initializing the cache, we now have 3 routes:

.. image:: /_static/images/quick-start/3-routes.png
   :class: console

Let's go back to our development environment and see the generated code:

.. code-block:: php
   :caption: app/controllers/DefaultController.php

	/**
	 * @route("info/{title}/{message}")
	 */
	public function information($title,$message='nothing'){
		$this->loadView('DefaultController/information.html');
	}

We need to pass the 2 variables to the view:

.. code-block:: php

	/**
	 * @route("info/{title}/{message}")
	 */
	public function information($title,$message='nothing'){
		$this->loadView('DefaultController/information.html',compact('title','message'));
	}
	
And we use our 2 variables in the associated twig view:

.. code-block:: html
   :caption: app/views/DefaultController/information.html

	<h1>{{title}}</h1>
	<div>{{message | raw}}</div>

We can test your page at ``http://127.0.0.1:8090/hello/info/Quick start/Ubiquity is quiet simple`` |br|
It's obvious

.. image:: /_static/images/quick-start/quiet-simple.png
   :class: bordered

.. |br| raw:: html

   <br />
