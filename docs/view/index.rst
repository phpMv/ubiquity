Views
==============
.. |br| raw:: html

   <br />

Ubiquity uses Twig as the default template engine (see `Twig documentation <https://twig.symfony.com/doc/2.x/>`_). |br|
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
    			$this->loadView("users/display.html",["message"=>$message,"type"=>$type]);
    		}
    	}
    }
    
In this case, it is usefull to call Compact for creating an array containing variables and their values :

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 6
      
    namespace controllers;
    
    class Users extends BaseController{
    	...
    	public function display($message,$type){
    			$this->loadView("users/display.html",compact("message","type"));
    		}
    	}
    }
   
Displaying in view
------------------

The view can then display the variables:

.. code-block:: html
   :caption: users/display.html
      
    <h2>{{type}}</h2>
    <div>{{message}}</div>
    
Variables may have attributes or elements you can access, too.

You can use a dot (.) to access attributes of a variable (methods or properties of a PHP object, or items of a PHP array), or the so-called "subscript" syntax ([]):

.. code-block:: smarty
      
    {{ foo.bar }}
    {{ foo['bar'] }}

Themes
==============
.. |br| raw:: html

   <br />

Ubiquity support themes wich can have it's own assets and views according to theme template to be rendered by controller. 
Each controller action can render a specif theme, or they can use the default theme configured at *config.php* file in templateEngineOptions => array("activeTheme" => "semantic").

Ubiquity is shipped with 3 default themes, bootstrap, foundation and semantic


Creating a theme
-------

To create a new theme you must:

1. Inside /app/views/themes create a new folder with desired *theme-name*
2. Inside /public/assets/ create a new folder with same name used on step above
3. Assets folder struct must contain the subfolders |br|

                                                   /css |br|
                                                   /scss |br|
                                                   /webfonts |br|
                                                   /others like /js, /img, /etc... |br|
4. View files must be created inside /themes/theme-name/Controller-name/action-name.html
5. Include ThemeManager in your controller 
    
    Inser after your namespace declaration - *use \\Ubiquity\\themes\\ThemesManager;*
6. Set the theme name inside your controller action

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 6
      
    namespace controllers;
    
    use \Ubiquity\themes\ThemesManager;
    
    class Users extends BaseController{
    	...
    	public function display($message,$type){
            ThemesManager::setActiveTheme('theme-name');
		      $this->loadView('@activeTheme/Users/display.html');
    		}
    	}
    }
