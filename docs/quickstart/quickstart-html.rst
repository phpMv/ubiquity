.. _quickstart-html:
Quick start with web tools 
==========================

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
Create the **quick-start** projet with **UbiquityMyAdmin** interface (the **-a** option)

.. code-block:: bash
   
   Ubiquity new quick-start -a

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
   :class: bordered

.. note:: If port 8090 is busy, you can start the server on another port using -p option.

.. code-block:: bash
   
   Ubiquity serve -p=8095
   

Controller
----------

Goto admin interface by clicking on the button **UbiquityMyAdmin**:

.. image:: /_static/images/quick-start-2/ubi-my-admin-btn.png

The web application **UbiquityMyAdmin** saves time in repetitive operations.

.. image:: /_static/images/quick-start-2/ubi-my-admin-interface.png
   :class: bordered

We go through it to create a controller.

Go to the **controllers** part, enter **DefaultController** in the **controllerName** field and create the controller:

.. image:: /_static/images/quick-start-2/create-controller-btn.png

The controller **DefaultController** is created:

.. image:: /_static/images/quick-start-2/controller-created.png
   :class: bordered

We can then edit ``app/controllers/DefaultController`` file in our favorite IDE:

.. code-block:: php
   :linenos:
   :caption: app/controllers/DefaultController.php
      
   namespace controllers;
    /**
    * Controller DefaultController
    **/
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
	The routing is defined with the annotation ``@route`` and is not done in a configuration file: |br|
	it's a design choice.
	
The **automated** parameter set to **true** allows the methods of our class to be defined as sub routes of the main route ``/hello``.

.. code-block:: php
   :linenos:
   :caption: app/controllers/DefaultController.php
      
	namespace controllers;
	 /**
	 * Controller DefaultController
	 * @route("/hello","automated"=>true)
	 **/
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

We can use the **web tools** for the cache re-initialization:

Go to the **Routes** section and click on the **re-init cache** button

.. image:: /_static/images/quick-start-2/re-init-cache-btn.png

The route now appears in the interface:

.. image:: /_static/images/quick-start-2/1-route.png
   :class: bordered

We can now test the page by clicking on the **GET** button or by going to the address ``http://127.0.0.1:8090/hello``


Action & route with parameters
------------------------------

We will now create an action (sayHello) with a parameter (name), and the associated route (to): |br|
The route will use the parameter **name** of the action:

Go to the **Controllers** section:

- click on the + button associated with DefaultController,
- then select **Add new action in..** item.

.. image:: /_static/images/quick-start-2/create-action-btn.png

Enter the action information in the following form:

.. image:: /_static/images/quick-start-2/create-action.png
   :class: bordered

After re-initializing the cache with the orange button, we can see the new route **hello/to/{name}**:

.. image:: /_static/images/quick-start-2/router-re-init-1.png


Check the route creation by going to the Routes section:

.. image:: /_static/images/quick-start-2/router-re-init-2.png

We can now test the page by clicking on the **GET** button:

.. image:: /_static/images/quick-start-2/test-action.png
   :class: bordered

We can see the result:

.. image:: /_static/images/quick-start-2/test-action-result.png
   :class: bordered

We could directly go to ``http://127.0.0.1:8090/hello/to/Mr SMITH`` address to test 

Action, route parameters & view
-------------------------------

We will now create an action (information) with tow parameters (title and message), the associated route (info), and a view to display the message: |br|
The route will use the two parameters of the action.

In the **Controllers** section, create another action on **DefaultController**:

.. image:: /_static/images/quick-start-2/create-action-btn.png

Enter the action information in the following form:

.. image:: /_static/images/quick-start-2/create-action-view.png
   :class: bordered

.. note:: The view checkbox is used to create the view associated with the action.

After re-initializing the cache, we now have 3 routes:

.. image:: /_static/images/quick-start-2/create-action-view-result.png

Let's go back to our development environment and see the generated code:

.. code-block:: php
   :caption: app/controllers/DefaultController.php

	/**
	 *@route("info/{title}/{message}")
	**/
	public function information($title,$message='nothing'){
		$this->loadView('DefaultController/information.html');
	}

We need to pass the 2 variables to the view:

.. code-block:: php

	/**
	 *@route("info/{title}/{message}")
	**/
	public function information($title,$message='nothing'){
		$this->loadView('DefaultController/information.html',compact('title','message'));
	}
	
And we use our 2 variables in the associated twig view:

.. code-block:: html
   :caption: app/views/DefaultController/information.html

	<h1>{{title}}</h1>
	<div>{{message | raw}}</div>

We can test our page at ``http://127.0.0.1:8090/hello/info/Quick start/Ubiquity is quiet simple`` |br|
It's obvious

.. image:: /_static/images/quick-start/quiet-simple.png
   :class: bordered

.. |br| raw:: html

   <br />
