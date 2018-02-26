Ubiquity Router
=================
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

Route name
^^^^^^^^^^
It is possible to specify the **name** of a road, this name then facilitates access to the associated url. |br|
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

