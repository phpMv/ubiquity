Ubiquity Controllers
====================
A controller is a PHP class inheriting from ``micro\\controllers\\Controller``, providing an entry point in the application.
Controllers and their methods define accessible URLs.

Controller creation
-------------------
The easiest way to create a controller is to do it from the devtools.

From the command prompt, go to the project folder.
To create the Products controller, use the command:
::
    Micro controller Products

The Products.php controller is created in the ``app/controllers`` folder of the project.

::
    <?php
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
          
          * The class must be in the ``app/controllers`` folder
          * The name of the class must match the name of the php file
          * It must inherit from ``ControllerBase`` and be defined in the namespace ``controllers``
          * It must override the abstract ``index`` method
