Webtools
========

.. note:: Webtools allow you to manage an Ubiquity application via a web interface.
   Since **Ubiquity 2.2.0**, webtools are in a separate `repository <https://github.com/phpMv/ubiquity-webtools>`_.

Installation
------------
Update the devtools if necessary to get started:

.. code-block:: bash
   
   composer global update

At the project creation
***********************

Create a projet with **webtools** (``-a`` option)

.. code-block:: bash
   
   Ubiquity new quick-start -a

In an existing project
**********************

In a console, go to the project folder and execute:

.. code-block:: bash
   
   Ubiquity admin

Starting
--------

Start the embedded web server, from the project folder:

.. code-block:: bash
   
   Ubiquity serve

go to the address: ``http://127.0.0.1:8090/Admin``

.. image:: /_static/images/webtools/interface.png
   :class: bordered

Customizing
-----------

Click on **customize** to display only the tools you use:

.. image:: /_static/images/webtools/customizing.png
   :class: bordered

.. image:: /_static/images/webtools/customized.png
   :class: bordered

Webtools modules
----------------

Routes
******
.. image:: /_static/images/webtools/headers/routes.png

Displays default (non REST) routes.

**Operations:**

 - Filter routes
 - Test routes (GET, POST...)
 - Initialize router cache
 
Controllers
***********
.. image:: /_static/images/webtools/headers/controllers.png

Displays non REST controllers.

**Operations:**

 - Create a controller (and optionally the view associated to the default **index** action)
 - Create an action in a controller (optionally the associated view, the associated route)
 - Create a special controller (CRUD or Auth)
 - Test an action (GET, POST...)

Models
******
.. image:: /_static/images/webtools/headers/models.png

Displays the metadatas of the models, allows to browse the entities.


**Operations:**

 - Create models from database
 - Generate models cache
 - Generate database script from existing models
 - Performs CRUD operations on models

Rest
****
.. image:: /_static/images/webtools/headers/rest.png

Displays an manage REST services.


**Operations:**

 - Re-initialize Rest cache and routes
 - Create a new Service (using an api)
 - Create a new resource (associated to a model)
 - Test and query a web service using http methods 
 - Performs CRUD operations on models
 
Cache
******
.. image:: /_static/images/webtools/headers/cache.png

Displays cache files.

**Operations:**

 - Delete or re-initialize models cache
 - Delete or re-initialize controllers cache
 - Delete other cache files

Maintenance
***********
.. image:: /_static/images/webtools/headers/maintenance.png

Allows to manage maintenance modes.

**Operations:**

 - Create or update a maintenance mode
 - De/Activate a maintenance mode
 - Delete a maintenance mode

Config
******
.. image:: /_static/images/webtools/headers/config.png

Allows the display and modification of the app configuration.

Git
***
.. image:: /_static/images/webtools/headers/git.png

Synchronizes the project using git.

**Operations:**

 - Configuration with external repositories
 - Commit
 - Push
 - Pull
 
 Themes
 ******
 .. image:: /_static/images/webtools/headers/themes.png

Manages Css themes.
 
 **Operations:**

 - Install an existing theme
 - Activate a theme
 - Create a new theme (eventually base on an existing theme)