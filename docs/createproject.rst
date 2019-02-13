Project creation
=================
After installing :doc:`install`, 
in a bash console, call the *new* command in the root folder of your web server : 

Samples
-------
A simple project

.. code-block:: bash
   
   Ubiquity new projectName
   
A project with Semantic-UI integration

.. code-block:: bash
   
   Ubiquity new projectName -q=semantic

A project with UbiquityMyAdmin interface and Semantic-UI integration

.. code-block:: bash
   
   Ubiquity new projectName -q=semantic -a

Installer arguments
-------------------

+------------+------------+---------------------------------+-----------+-----------------------+
| short name | name       | role                            | default   | Allowed values        |
+============+============+=================================+===========+=======================+
|      b     | dbName     | Sets the database name.         |           |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      s     | serverName | Defines the db server address.  | 127.0.0.1 |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      p     | port       | Defines the db server port.     |      3306 |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      u     | user       | Defines the db server user.     |      root |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      w     | password   | Defines the db server password. |        '' |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      q     | phpmv      | Integrates phpMv-UI toolkit.    |     false | semantic,bootstrap,ui |
+------------+------------+---------------------------------+-----------+-----------------------+
|      m     | all-models | Creates all models from db.     |     false |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      a     | admin      | Adds UbiquityMyAdmin interface. |     false |                       |
+------------+------------+---------------------------------+-----------+-----------------------+

Arguments usage
---------------

short names
^^^^^^^^^^^
Example of creation of the **blog** project, connected to the **blogDb** database, with generation of all models

.. code-block:: bash
   
   Ubiquity new blog -b=blogDb -m=true 

long names
^^^^^^^^^^^
Example of creation of the **blog** project, connected to the **bogDb** database, with generation of all models and integration of phpMv-toolkit

.. code-block:: bash
   
   Ubiquity new blog --dbName=blogDb --all-models=true --phpmv=semantic 

Testing
-------

To start the embedded web server and test your pages, run from the application root folder:

.. code-block:: bash
   
   Ubiquity serve
   
The web server is started at ``127.0.0.1:8090``
