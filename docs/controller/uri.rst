URLs
====
like many other frameworks, if you are using router with it's default behavior, there is a one-to-one relationship between a URL string and its corresponding controller class/method.
The segments in a URI normally follow this pattern:
::
    example.com/controller/method/param
    example.com/controller/method/param1/param2

Default method
--------------

When the URL is composed of a single part, corresponding to the name of a controller, the index method of the controller is automatically called :

**URL :**
::
    example.com/Products
    example.com/Products/index

**Controller :**

.. code-block:: php
   :linenos:
   :caption: app/controllers/Products.php
   
   class Products extends ControllerBase{
       public function index(){
           //Default action
       } 
   }

Required parameters
-------------------

If the requested method requires parameters, they must be passed in the URL:

**Controller :**

.. code-block:: php
   :linenos:
   :caption: app/controllers/Products.php
   
   class Products extends ControllerBase{
       public function display($id){} 
   }

**Valid Urls :**
::
    example.com/Products/display/1
    example.com/Products/display/10/
    example.com/Products/display/ECS

Optional parameters
-------------------
The called method can accept optional parameters.

If a parameter is not present in the URL, the default value of the parameter is used.

**Controller :**

.. code-block:: php
   :caption: app/controllers/Products.php
   
   class Products extends ControllerBase{
       public function sort($field,$order="ASC"){} 
   }

**Valid Urls :**
::
    example.com/Products/sort/name (uses "ASC" for the second parameter)
    example.com/Products/sort/name/DESC
    example.com/Products/sort/name/ASC

Case sensitivity
----------------
On Unix systems, the name of the controllers is case-sensitive.

**Controller :**

.. code-block:: php
   :caption: app/controllers/Products.php
   
   class Products extends ControllerBase{
       public function caseInsensitive(){} 
   }

**Urls :**
::
    example.com/Products/caseInsensitive (valid)
    example.com/Products/caseinsensitive (valid because the method names are case insensitive)
    example.com/products/caseInsensitive (invalid since the products controller does not exist)

Routing customization
---------------------
The :doc:`router` and annotations of controller classes allow you to customize URLs.
