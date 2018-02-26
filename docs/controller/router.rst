Ubiquity Router
=================
.. |br| raw:: html

   <br />
Routing can be used in addition to the default mechanism that associates *controller/action/{parameters}* with an url. |br|
Routing works by using the **@route** annotation on controller methods.

Routes definition
-------------------
Creation
^^^^^^^
.. code-block:: php
   :linenos:
   :caption: app/controllers/Products.php
   :emphasize-lines: 7,8,9
   
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