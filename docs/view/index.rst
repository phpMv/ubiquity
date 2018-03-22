Views
==============
.. |br| raw:: html

   <br />

Ubiquity uses Twig as the default template engine (see Link Twig documentation <https://twig.symfony.com/doc/2.x/>). |br|
The views are located in the **app/views** folder. They must have the **.html** extension for being interpreted by Twig.

Loading
-------
Views are loaded from controllers:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 6
      
    namespace controllers;
    
    class Users extends BaseController{
    	...
    	public function index(){
    			$this->loadView("index.html");
    		}
    	}
    }

Loading and passing variables
-----------------------------
Variables are passed to the view with an associative array. Each key creates a variable of the same name in the view.

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 6
      
    namespace controllers;
    
    class Users extends BaseController{
    	...
    	public function display($message,$type){
    			$this->loadView("users/display.html",["msg"=>$message,"type"=>$type]);
    		}
    	}
    }
    
The view can then display the variables:

.. code-block:: html
   :caption: users/display.html
      
    <h2>{{type}}</h2>
    <div>{{msg}}</div>
    
Variables may have attributes or elements you can access, too. The visual representation of a variable depends heavily on the application providing it.

You can use a dot (.) to access attributes of a variable (methods or properties of a PHP object, or items of a PHP array), or the so-called "subscript" syntax ([]):

.. code-block:: html
      
    {{ foo.bar }}
    {{ foo['bar'] }}

