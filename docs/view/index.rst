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
    
Ubiquity extra functions
------------------------
Global `app` variable provides access to predefined Ubiquity Twig features:

- `app` is an instance of Framework and provides access to public methods of this class.

Get framework installed version:

.. code-block:: smarty

    {{ app.version() }}


Return the active controller and action names:

.. code-block:: smarty

    {{ app.getController() }}
    {{ app.getAction() }}

Return global wrapper classes :

For request:

.. code-block:: smarty

    {{ app.getRequest().isAjax() }}

For session :

.. code-block:: smarty

    {{ app.getSession().get('homePage','index') }}

Assets
======
Assets correspond to javascript files, style sheets, fonts, images to include in your application.
They are located from the **public/assets** folder. |br|
It is preferable to separate resources into sub-folders by type.

Assets integration with twig
++++++++++++++++++++++++++++
Local files
~~~~~~~~~~

```bash
public/assets
     ├ css
     │    ├ style.css
     │    └ semantic.min.css
     └ js
          └ jquery.min.js
           
```

.. code-block:: smarty

    {{ css('css/style.css') }}
    {{ css('css/semantic.min.css') }}
    
    {{ js('js/jquery.min.js') }}

CDN files
~~~~~~~~~~

.. code-block:: smarty

    {{ css('https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css') }}
    
    {{ js('https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js') }}



Themes
======

Ubiquity support themes wich can have it's own assets and views according to theme template to be rendered by controller. 
Each controller action can render a specif theme, or they can use the default theme configured at *config.php* file in templateEngineOptions => array("activeTheme" => "semantic").

Ubiquity is shipped with 3 default themes, bootstrap, foundation and semantic


Creating a theme
----------------

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
