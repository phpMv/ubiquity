Quick start guide
=================

Install Composer
----------------
**ubiquity** utilizes Composer to manage its dependencies. So, before using, you will need to make sure you have `Composer <http://getcomposer.org/>`_ installed on your machine.

Install Ubiquity-devtools
-------------------------
Download the Ubiquity-devtools installer using Composer.

.. code-block:: bash
   
   composer global require phpmv/ubiquity-devtools
   
Project creation
----------------
Create the **quick-start** projet with UbiquityMyAdmin interface and Semantic-UI integration

.. code-block:: bash
   
   Ubiquity new quick-start -q=semantic -a

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

.. image:: _static/images/quick-start/quick-start-main.png

.. note:: If port 8090 is busy, you can start the server on another port using -p option.

.. code-block:: bash
   
   Ubiquity serve -p=8095
   

Controller
----------

The console application **dev-tools** saves time in repetitive operations.
We go through it to create a controller.

.. code-block:: bash
   
   Ubiquity controller DefaultController
   
.. image:: _static/images/quick-start/controller-creation.png

We can then edit ``app/controllers/DefaultController`` file in our favorite IDE:

.. code-block:: php
   
   namespace controllers;
    /**
    * Controller DefaultController
    **/
   class DefaultController extends ControllerBase{
   
    	public function index(){}
   
   }
