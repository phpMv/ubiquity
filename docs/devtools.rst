Devtools usage
==============

Project creation
----------------
See :doc:`createproject` for project creation.

.. note:: For all other commands, you must be in your project folder or one of its subfolders.

Controller creation
-------------------
Creates the controller ``controllers\ClientController`` class in ``app/controllers/ClientController.php``

.. code-block:: bash
   
   Ubiquity controller ClientController

Creates the controller ``controllers\ClientController`` class in ``app/controllers/ClientController.php`` and the associated view in ``app/views/ClientController/index.html``

.. code-block:: bash
   
   Ubiquity controller ClientController -v
   
Model creation
--------------

.. note:: Optionally check the database connection settings in the app/config/config.php file before running these commands.

To generate a model corresponding to the **user** table in database:

.. code-block:: bash
   
   Ubiquity model user

All models creation
-------------------

For generating all models from the database:

.. code-block:: bash
   
   Ubiquity all-models

Cache initialization
--------------------
To initialize the cache for routing (based on annotations in controllers) and orm (based on annotations in models) :

.. code-block:: bash
   
   Ubiquity init-cache
