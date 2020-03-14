.. _views:
Views
==============
.. |br| raw:: html

   <br />

Ubiquity uses Twig as the default template engine (see `Twig documentation <https://twig.symfony.com/doc/2.x/>`_). |br|
The views are located in the **app/views** folder. They must have the **.html** extension for being interpreted by Twig.

Ubiquity can also be used with a PHP view system, to get better performance, or simply to allow the use of php in the views.

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

Default view loading
~~~~~~~~~~~~~~~~~~~~
If you use the default view naming method : |br|
The default view associated to an action in a controller is located in ``views/controller-name/action-name`` folder:

.. code-block:: bash

	views
	     │
	     └ Users
	         └ info.html
	         

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 6
      
    namespace controllers;
    
    class Users extends BaseController{
    	...
    	public function info(){
    			$this->loadDefaultView();
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
Global ``app`` variable provides access to predefined Ubiquity Twig features:

- ``app`` is an instance of ``Framework`` and provides access to public methods of this class.

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
    
see `Framework class in API <https://api.kobject.net/ubiquity/class_ubiquity_1_1core_1_1_framework.html>`_ for more.

PHP view loading
----------------

Disable if necessary Twig in the configuration file by deleting the **templateEngine** key. 

Then create a controller that inherits from ``SimpleViewController``, or ``SimpleViewAsyncController`` if you use **Swoole** or **Workerman**:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 5
      
    namespace controllers;
    
    use Ubiquiy\controllers\SimpleViewController;
    
    class Users extends SimpleViewController{
    	...
    	public function display($message,$type){
    			$this->loadView("users/display.php",compact("message","type"));
    		}
    	}
    }

.. note::
   In this case, the functions for loading assets and themes are not supported.


Assets
======
Assets correspond to javascript files, style sheets, fonts, images to include in your application. |br|
They are located from the **public/assets** folder. |br|
It is preferable to separate resources into sub-folders by type.

Assets integration with twig
++++++++++++++++++++++++++++
Local files
~~~~~~~~~~

.. code-block:: bash

	public/assets
	     ├ css
	     │   ├ style.css
	     │   └ semantic.min.css
	     └ js
	         └ jquery.min.js

Integration of css or js files :

.. code-block:: smarty
    
    {{ css('css/style.css') }}
    {{ css('css/semantic.min.css') }}
    
    {{ js('js/jquery.min.js') }}

CDN files
~~~~~~~~~~

.. code-block:: smarty

    {{ css('https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css') }}
    
    {{ js('https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js') }}


CDN with extra parameters:

.. code-block:: smarty

    {{ css('https://cdn.jsdelivr.net/npm/foundation-sites@6.5.3/dist/css/foundation.min.css',{crossorigin: 'anonymous',integrity: 'sha256-/PFxCnsMh+...'}) }}
    

Themes
======

Ubiquity support themes wich can have it's own assets and views according to theme template to be rendered by controller. 
Each controller action can render a specific theme, or they can use the default theme configured at *config.php* file in ``templateEngineOptions => array("activeTheme" => "semantic")``.

Ubiquity is shipped with 3 default themes : **Bootstrap**, **Foundation** and **Semantic-UI**.


Installing a theme
------------------

With devtools, run :

.. code-block:: bash

	Ubiquity install-theme bootstrap
	
The installed theme is one of **bootstrap**, **foundation** or **semantic**.

With **webtools**, you can do the same, provided that the **devtools** are installed and accessible (Ubiquity folder added in the system path) :

.. image:: /_static/images/views/themesManager-install-theme.png


Creating a new theme
--------------------

With devtools, run :

.. code-block:: bash

	Ubiquity create-theme myTheme


Creating a new theme from Bootstrap, Semantic...

With devtools, run :

.. code-block:: bash

	Ubiquity create-theme myBootstrap -x=bootstrap
	

With **webtools** :

.. image:: /_static/images/views/themesManager-create-theme.png


Theme functioning and structure
-------------------------------
Structure
~~~~~~~~~

**Theme view folder**

The views of a theme are located from the **app/views/themes/theme-name** folder

.. code-block:: bash

	app/views
		└ themes
		       ├ bootstrap
		       │         └ main
		       │              ├ vHeader.html
		       │              └ vFooter.html
		       └ semantic
		                └ main
		                     ├ vHeader.html
		                     └ vFooter.html

The controller base class is responsible for loading views to define the header and footer of each page  :

.. code-block:: php
   :linenos:
   :caption: app/controllers/ControllerBase.php
   
	<?php
	namespace controllers;
	
	use Ubiquity\controllers\Controller;
	use Ubiquity\utils\http\URequest;
	
	/**
	 * ControllerBase.
	 **/
	abstract class ControllerBase extends Controller{
		protected $headerView = "@activeTheme/main/vHeader.html";
		protected $footerView = "@activeTheme/main/vFooter.html";
		
		public function initialize() {
			if (! URequest::isAjax ()) {
				$this->loadView ( $this->headerView );
			}
		}
		public function finalize() {
			if (! URequest::isAjax ()) {
				$this->loadView ( $this->footerView );
			}
		}
	}


**Theme assets folder**

The assets of a theme are created inside ``public/assets/theme-name`` folder.

The structure of the assets folder is often as follows :

.. code-block:: bash
    
	public/assets/bootstrap
					├ css
					│   ├ style.css
					│   └ all.min.css
					├ scss
					│   ├ myVariables.scss
					│   └ app.scss
					├ webfonts
					│
					└ img


Change of the active theme
--------------------------
Persistent change
~~~~~~~~~~~~~~~~~

**activeTheme** is defined in ``app/config/config.php`` with ``templateEngineOptions => array("activeTheme" => "semantic")``

The active theme can be changed with **devtools** :

.. code-block:: bash
    
    Ubiquity config:set --templateEngineOptions.activeTheme=bootstrap
    
It can also be done from the home page, or with **webtools** :

**From the home page :**

.. image:: /_static/images/views/change-theme-home.png

**From the webtools :**

.. image:: /_static/images/views/change-theme-webtools.png


This change can also be made at runtime :

**From a controller :**

.. code-block:: php
    
    ThemeManager::saveActiveTheme('bootstrap');

Non-persistent local change
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To set a specific theme for all actions within a controller, the simplest method is to override the controller's **initialize** method :


.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 9
      
    namespace controllers;
    
    use \Ubiquity\themes\ThemesManager;
    
    class Users extends BaseController{
    
	    public function initialize(){
	    	parent::intialize();
	    	ThemesManager::setActiveTheme('bootstrap');
	    }
	}

Or if the change should only concern one action :

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 8
      
    namespace controllers;
    
    use \Ubiquity\themes\ThemesManager;
    
    class Users extends BaseController{
    
	    public function doStuff(){
	    	ThemesManager::setActiveTheme('bootstrap');
	    	...
	    }
	}

Conditional theme change, regardless of the controller :

Example with a modification of the theme according to a variable passed in the URL

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   
   use Ubiquity\themes\ThemesManager;
   use Ubiquity\utils\http\URequest;
   
   ...
   
   ThemesManager::onBeforeRender(function(){
		if(URequest::get("th")=='bootstrap'){
			ThemesManager::setActiveTheme("bootstrap");
		}
	});

View and assets loading
-----------------------

Views
~~~~~

For loading a view from the **activeTheme** folder, you can use the **@activeTheme** namespace :

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 6
      
    namespace controllers;
    
    class Users extends BaseController{
    
	    public function action(){
	    	$this->loadView('@activeTheme/action.html');
	    	...
	    }
	}

If the **activeTheme** is **bootstrap**, the loaded view is ``app/views/themes/bootstrap/action.html``.

DefaultView
~~~~~~~~~~

If you follow the Ubiquity view naming model, the default view loaded for an action in a controller when a theme is active is :
``app/views/themes/theme-name/controller-name/action-name.html``.

For example, if the activeTheme is bootstrap, the default view for the action display in the Users controller must be loacated in 
``app/views/themes/bootstrap/Users/display.html``.

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 6
      
    namespace controllers;
    
    class Users extends BaseController{
    
	    public function display(){
	    	$this->loadDefaultView();
	    	...
	    }
	}
	
.. note::
   The devtools commands to create a controller or an action and their associated view use the **@activeTheme** folder if a theme is active.
   
   .. code-block:: bash
      
      Ubiquity controller Users -v
      
      Ubiquity action Users.display -v


Assets loading
--------------

The mechanism is the same as for the views : ``@activeTheme`` namespace refers to the ``public/assets/theme-name/`` folder

.. code-block:: smarty
    
    {{ css('@activeTheme/css/style.css') }}
    
    {{ js('@activeTheme/js/scripts.js') }}

If the **bootstrap** theme is active, |br|
the assets folder is ``public/assets/bootstrap/``.

Css compilation
---------------

For Bootstrap or foundation, install sass:

.. code-block:: bash
   
   npm install -g sass

Then run from the project root folder:

**For bootstrap:**

.. code-block:: bash
   
   ssass public/assets/bootstrap/scss/app.scss public/assets/bootstrap/css/style.css --load-path=vendor

**For foundation:**

.. code-block:: bash
   
   ssass public/assets/foundation/scss/app.scss public/assets/foundation/css/style.css --load-path=vendor
