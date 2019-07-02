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

Start the embedded web server:

In the project folder,

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
++++++
Displays default (non REST) routes.

**Operations:**

 - Filter routes
 - Test routes (GET, POST...)
 - Init router cache
 
Controllers
+++++++++++
Displays non REST controllers.

**Operations:**

 - Create a controller (and optionally the view associated to the default **index** action)
 - Create an action in a controller (optionally the associated view, the associated route)
 - Create a special controller (CRUD or Auth)
 - Test an action (GET, POST...)

Models
++++++
Maintenance
+++++++++++