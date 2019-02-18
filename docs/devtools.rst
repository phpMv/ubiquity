Devtools usage
==============

Project creation
----------------
See :doc:`createproject` to create a project.

.. tip:: For all other commands, you must be in your project folder or one of its subfolders.

.. tip:: 
   The ``.ubiquity`` folder created automatically with the project allows the devtools to find the root folder of the project.
   If it has been deleted or is no longer present, you must recreate this empty folder.

Controller creation
-------------------
Specifications
++++++++++++++

- command : `controller`
- Argument : `controller-name`
- aliases : `create-controller`

Parameters
++++++++++

+------------+------------+------------------------------------+-----------+-----------------------+
| short name | name       | role                               | default   | Allowed values        |
+============+============+====================================+===========+=======================+
|      v     | view       | Creates the associated view index. |   true    | true, false           |
+------------+------------+------------------------------------+-----------+-----------------------+

Samples:
+++++++
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
